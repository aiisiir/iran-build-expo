<!doctype html>
<html lang="fa-IR" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>ورود — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin login">
<form class="login-box" method="post" action="{{ route('admin.login') }}">
    @csrf
    <h1>ورود به پنل</h1>
    @error('email')<p class="flash err" role="alert">{{ $message }}</p>@enderror
    <label>ایمیل <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" dir="ltr"></label>
    <label>رمز عبور <input type="password" name="password" required autocomplete="current-password" dir="ltr"></label>
    <label class="check"><input type="checkbox" name="remember" value="1"> مرا به خاطر بسپار</label>
    <button type="submit">ورود</button>
</form>
</body>
</html>
