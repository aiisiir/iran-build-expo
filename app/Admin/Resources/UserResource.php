<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    public bool $copyable = false;

    public bool $adminOnly = true;

    public function model(): string
    {
        return User::class;
    }

    public function key(): string
    {
        return 'users';
    }

    public function title(): string
    {
        return 'کاربران';
    }

    public function fields(?Model $model): array
    {
        return [
            Field::text('name', 'نام', ['required', 'string', 'max:120']),
            Field::text('email', 'ایمیل', ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($model?->id)]),
            Field::password('password', 'رمز عبور', ! $model?->exists),
            Field::select('role', 'نقش', Options::panelRoles(), ['required', 'in:admin,editor']),
            Field::text('position', 'سمت (فقط نمایشی)', ['nullable', 'string', 'max:100']),
            Field::checkbox('is_active', 'فعال'),
        ];
    }

    public function columns(): array
    {
        return ['نام' => 'name', 'ایمیل' => 'email', 'نقش' => 'role', 'سمت' => 'position', 'فعال' => 'is_active', 'آخرین ورود' => 'last_login_at'];
    }

    /** The panel must always keep at least one active admin, or nobody can log in to fix it. Superadmins are read-only here. */
    public function check(array $data, ?Model $model): void
    {
        // A superadmin is created, promoted, demoted and removed in the database only; the panel never touches one.
        if ($model?->isSuper()) {
            throw ValidationException::withMessages(['role' => 'مدیر ارشد فقط از طریق پایگاه داده قابل تغییر است.']);
        }

        $demoted = ! in_array($data['role'] ?? null, ['admin', 'superadmin'], true) || ! ($data['is_active'] ?? false);

        if ($model?->exists && $model->isAdmin() && $model->is_active && $demoted
            && ! User::whereIn('role', ['admin', 'superadmin'])->where('is_active', true)->whereKeyNot($model->id)->exists()) {
            throw ValidationException::withMessages(['role' => 'حداقل یک مدیر فعال باید باقی بماند.']);
        }
    }

    public function canDelete(Model $model): bool
    {
        return ! $model->isSuper() && $model->id !== auth()->id();
    }
}
