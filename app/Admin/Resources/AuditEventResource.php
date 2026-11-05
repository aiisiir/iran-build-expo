<?php

namespace App\Admin\Resources;

use App\Admin\Resource;
use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;

class AuditEventResource extends Resource
{
    public bool $adminOnly = true;

    public bool $readOnly = true;

    public function model(): string
    {
        return AuditEvent::class;
    }

    public function key(): string
    {
        return 'audit';
    }

    public function title(): string
    {
        return 'گزارش فعالیت‌ها';
    }

    public function fields(?Model $model): array
    {
        return [];
    }

    public function columns(): array
    {
        return ['زمان' => 'created_at', 'کاربر' => 'user_id', 'رویداد' => 'event', 'مورد' => 'subject_type', 'شناسه' => 'subject_id', 'IP' => 'ip', 'فیلدها' => 'changes'];
    }
}
