<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class ActivityLogService
{
    public function log(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values_json' => $oldValues,
            'new_values_json' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function logModelEvent(string $action, string $entityType, EloquentModel $model): void
    {
        $oldValues = null;
        $newValues = null;

        if ($action === 'updated') {
            $oldValues = $model->getOriginal();
            $newValues = $model->getAttributes();
        } elseif ($action === 'created') {
            $newValues = $model->getAttributes();
        } elseif (in_array($action, ['deleted', 'restored'], true)) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getAttributes();
        }

        $this->log($action, $entityType, (int) $model->getKey(), $oldValues, $newValues);
    }
}

