<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController
{
    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 900;

    public function show(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $key = Str::lower($data['email']).'|'.$request->ip(); // per account+IP, so an attacker cannot lock the real admin out from elsewhere

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            throw ValidationException::withMessages(['email' => "تلاش ناموفق زیاد بود. {$minutes} دقیقه دیگر دوباره تلاش کنید."]);
        }

        if (! Auth::attempt([...$data, 'is_active' => true], $request->boolean('remember'))) {
            RateLimiter::hit($key, self::LOCK_SECONDS);
            AuditEvent::log('login_failed');
            throw ValidationException::withMessages(['email' => 'ایمیل یا رمز عبور درست نیست.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->saveQuietly();
        AuditEvent::log('login');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        AuditEvent::log('logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
