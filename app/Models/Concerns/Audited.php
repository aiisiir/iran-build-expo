<?php

namespace App\Models\Concerns;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;

/** Writes create/update/delete/restore to the audit log (changed field names only, never values). */
trait Audited
{
    protected static function bootAudited(): void
    {
        foreach (['created', 'updated', 'deleted', 'restored'] as $event) {
            static::{$event}(function (Model $model) use ($event): void {
                $fields = $event === 'updated' ? array_keys($model->getChanges()) : [];
                AuditEvent::log($event, $model, array_values(array_diff($fields, ['updated_at', 'remember_token'])));
            });
        }
    }
}
