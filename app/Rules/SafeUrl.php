<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Internal path (/x) or http, https, mailto, tel. Blocks javascript:, data:, protocol-relative URLs. */
class SafeUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        $internal = str_starts_with($value, '/') && ! str_starts_with($value, '//') && ! str_contains($value, '\\');
        $external = (bool) preg_match('#^(https?://[^\s]+|mailto:[^\s]+|tel:[+0-9]+)$#i', $value);

        if (! $internal && ! $external) {
            $fail('نشانی معتبر نیست (باید با / یا http:// یا https:// یا mailto: یا tel: شروع شود).');
        }
    }
}
