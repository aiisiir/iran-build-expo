<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Validation shared by the public badge form and the admin editor. */
final class BadgeRules
{
    /** Persian/Arabic digits become Latin before validation; phone keeps only digits and a leading +. */
    public static function normalize(Request $request): void
    {
        $request->merge([
            'phone' => preg_replace('/[^\d+]/', '', Digits::toLatin((string) $request->input('phone'))),
            'age' => Digits::toLatin((string) $request->input('age')) ?: null,
        ]);
    }

    public static function base(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'badge_type_id' => ['required', Rule::exists('badge_types', 'id')->where('is_active', true)],
            'phone' => ['required', 'regex:/^\+?\d{8,15}$/'],
            'email' => ['required', 'email', 'max:191'],
            'gender' => ['required', 'in:male,female'],
            'age' => ['nullable', 'integer', 'between:5,120'],
            'address' => ['nullable', 'string', 'max:500'],
            'education_degree' => ['nullable', 'string', 'max:60'],
            'education_field' => ['nullable', 'string', 'max:120'],
            'activity_field' => ['nullable', 'string', 'max:120'],
        ];
    }
}
