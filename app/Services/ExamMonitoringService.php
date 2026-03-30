<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\ExamSessionLog;
use App\Models\SuspiciousActivityLog;
use App\Models\TabSwitchLog;
use Illuminate\Support\Carbon;

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

        $this->applyCounterSideEffect($attempt, $eventType);
    }

    private function applyCounterSideEffect(ExamAttempt $attempt, string $eventType): void
    {
        $attempt->refresh();

        if (in_array($eventType, ['visibility_hidden', 'window_blur'], true)) {
            $attempt->focus_loss_count++;
            $attempt->save();

            if ($attempt->focus_loss_count >= 5) {
                $this->flagSuspicious($attempt, 'tab_switch_excessive', 'high', 'Focus loss threshold reached.');
            }
        }

        if ($eventType === 'refresh') {
            $attempt->refresh_count++;
            $attempt->save();

            if ($attempt->refresh_count >= 3) {
                $this->flagSuspicious($attempt, 'refresh_excessive', 'medium', 'Refresh threshold reached.');
            }
        }

        if ($eventType === 'duplicate_session') {
            $this->flagSuspicious($attempt, 'duplicate_session', 'high', 'Possible duplicate session detected.');
        }

        if ($eventType === 'reconnect') {
            $this->flagSuspicious($attempt, 'unusual_reconnect', 'low', 'Reconnect detected during attempt.');
        }
    }

    public function flagSuspicious(ExamAttempt $attempt, string $activityType, string $severity, string $note): void
    {
        $attempt->suspicious_flag = true;
        $attempt->save();

        SuspiciousActivityLog::create([
            'user_id' => $attempt->student_id,
            'exam_attempt_id' => $attempt->id,
            'activity_type' => $activityType,
            'severity' => $severity,
            'note' => $note,
        ]);
    }
}

