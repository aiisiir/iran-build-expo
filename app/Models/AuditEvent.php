<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'event', 'subject_type', 'subject_id', 'ip', 'user_agent', 'changes'])]
class AuditEvent extends Model
{
    public const UPDATED_AT = null;

    public static function log(string $event, ?Model $subject = null, array $changes = []): void
    {
        $request = request();

        static::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 191) ?: null,
            'changes' => $changes ? json_encode($changes) : null,
        ]);
    }
}
