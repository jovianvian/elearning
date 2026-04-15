<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\ExamSessionLog;
use App\Models\SuspiciousActivityLog;
use App\Models\TabSwitchLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExamMonitoringService
{
    public function logEvent(ExamAttempt $attempt, string $eventType, array $metadata = []): void
    {
        $now = Carbon::now();

        ExamSessionLog::create([
            'exam_attempt_id' => $attempt->id,
            'user_id' => $attempt->student_id,
            'event_type' => $eventType,
            'event_time' => $now,
            'metadata_json' => $metadata ?: null,
        ]);

        if (in_array($eventType, ['visibility_hidden', 'visibility_visible', 'window_blur', 'window_focus'], true)) {
            TabSwitchLog::create([
                'exam_attempt_id' => $attempt->id,
                'user_id' => $attempt->student_id,
                'event_type' => $eventType,
                'event_time' => $now,
            ]);
        }

        $this->applyCounterSideEffect($attempt, $eventType, $metadata, $now);
    }

    private function applyCounterSideEffect(ExamAttempt $attempt, string $eventType, array $metadata, Carbon $now): void
    {
        $attempt->refresh();

        if (in_array($eventType, ['visibility_hidden', 'window_blur'], true)) {
            $attempt->focus_loss_count++;
            $attempt->save();
            $this->incrementTabSwitchCount($attempt);

            if ($attempt->focus_loss_count >= 5) {
                $this->flagSuspicious($attempt, 'focus_loss_excessive', 'high', 'Focus loss threshold reached.', $metadata, $now);
            }
        }

        if ($eventType === 'refresh') {
            $attempt->refresh_count++;
            $attempt->save();

            if ($attempt->refresh_count >= 3) {
                $this->flagSuspicious($attempt, 'refresh_excessive', 'medium', 'Refresh threshold reached.', $metadata, $now);
            }
        }

        if (in_array($eventType, ['duplicate_session', 'multiple_tabs_detected'], true)) {
            $this->flagSuspicious($attempt, 'duplicate_session', 'high', 'Possible duplicate session detected.', $metadata, $now);
        }

        if ($eventType === 'reconnect') {
            $recentReconnects = $this->countRecentEvents($attempt->id, ['reconnect'], 10);
            if ($recentReconnects >= 3) {
                $this->flagSuspicious($attempt, 'reconnect_excessive', 'medium', 'Repeated reconnect detected during attempt.', $metadata, $now);
            } else {
                $this->flagSuspicious($attempt, 'reconnect_detected', 'low', 'Reconnect detected during attempt.', $metadata, $now);
            }
        }

        if (in_array($eventType, ['visibility_hidden', 'window_blur', 'tab_switch'], true)) {
            $rapidSwitches = $this->countRecentEvents($attempt->id, ['visibility_hidden', 'window_blur', 'tab_switch'], 5);
            if ($rapidSwitches >= 6) {
                $this->flagSuspicious($attempt, 'tab_switch_excessive', 'high', 'Rapid tab switching threshold reached.', $metadata, $now);
            }
        }

        $tabId = trim((string) ($metadata['tab_id'] ?? ''));
        if ($tabId !== '') {
            $distinctTabCount = $this->countDistinctRecentTabIds($attempt->id, 15);
            if ($distinctTabCount > 1) {
                $this->flagSuspicious($attempt, 'multiple_tabs_detected', 'high', 'Multiple active tab identifiers detected.', $metadata, $now);
            }
        }
    }

    public function flagSuspicious(
        ExamAttempt $attempt,
        string $activityType,
        string $severity,
        string $note,
        array $context = [],
        ?Carbon $detectedAt = null
    ): void
    {
        $detectedAt = $detectedAt ?? Carbon::now();
        $attempt->suspicious_flag = true;
        $attempt->save();

        $existing = SuspiciousActivityLog::query()
            ->where('exam_attempt_id', $attempt->id)
            ->where('activity_type', $activityType)
            ->where('created_at', '>=', $detectedAt->copy()->subMinutes(10))
            ->latest('id')
            ->first();

        if ($existing) {
            $existing->update([
                'severity' => $severity,
                'note' => $note,
                'event_count' => ((int) $existing->event_count) + 1,
                'last_detected_at' => $detectedAt,
                'context_json' => $context !== [] ? $context : $existing->context_json,
            ]);
            return;
        }

        SuspiciousActivityLog::create([
            'user_id' => $attempt->student_id,
            'exam_attempt_id' => $attempt->id,
            'activity_type' => $activityType,
            'severity' => $severity,
            'note' => $note,
            'event_count' => 1,
            'last_detected_at' => $detectedAt,
            'context_json' => $context !== [] ? $context : null,
        ]);
    }

    private function incrementTabSwitchCount(ExamAttempt $attempt): void
    {
        $attempt->tab_switch_count = ((int) $attempt->tab_switch_count) + 1;
        $attempt->save();
    }

    private function countRecentEvents(int $attemptId, array $eventTypes, int $withinMinutes): int
    {
        return ExamSessionLog::query()
            ->where('exam_attempt_id', $attemptId)
            ->whereIn('event_type', $eventTypes)
            ->where('event_time', '>=', Carbon::now()->subMinutes($withinMinutes))
            ->count();
    }

    private function countDistinctRecentTabIds(int $attemptId, int $withinMinutes): int
    {
        return ExamSessionLog::query()
            ->where('exam_attempt_id', $attemptId)
            ->whereNotNull('metadata_json')
            ->where('event_time', '>=', Carbon::now()->subMinutes($withinMinutes))
            ->select(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(metadata_json, '$.tab_id')) as tab_id"))
            ->groupBy('tab_id')
            ->havingRaw("tab_id IS NOT NULL AND tab_id <> ''")
            ->get()
            ->count();
    }
}
