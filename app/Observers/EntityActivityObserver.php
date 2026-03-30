<?php

namespace App\Observers;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class EntityActivityObserver
{
    public function created(EloquentModel $model): void
    {
        app(ActivityLogService::class)->logModelEvent('created', $this->entityType($model), $model);
    }

    public function updated(EloquentModel $model): void
    {
        app(ActivityLogService::class)->logModelEvent('updated', $this->entityType($model), $model);
    }

    public function deleted(EloquentModel $model): void
    {
        app(ActivityLogService::class)->logModelEvent('deleted', $this->entityType($model), $model);
    }

    public function restored(EloquentModel $model): void
    {
        app(ActivityLogService::class)->logModelEvent('restored', $this->entityType($model), $model);
    }

    private function entityType(EloquentModel $model): string
    {
        return $model->getTable();
    }
}
