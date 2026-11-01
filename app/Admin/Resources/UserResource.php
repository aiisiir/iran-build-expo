<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class UserResource extends Resource
{
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
            Field::select('role', 'نقش', Options::roles(), ['required', 'in:admin,editor']),
            Field::text('position', 'سمت (فقط نمایشی)', ['nullable', 'string', 'max:100']),
            Field::checkbox('is_active', 'فعال'),
        ];
    }

    public function columns(): array
    {
        return ['نام' => 'name', 'ایمیل' => 'email', 'نقش' => 'role', 'سمت' => 'position', 'فعال' => 'is_active', 'آخرین ورود' => 'last_login_at'];
    }

    public function canDelete(Model $model): bool
    {
        return $model->id !== auth()->id();
    }
}
