<!doctype html>
<html lang="fa-IR" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'پنل مدیریت') — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ url('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @if (is_file(public_path('vendor/jodit/jodit.min.css')))
        <link rel="stylesheet" href="{{ asset('vendor/jodit/jodit.min.css') }}">
    @endif
</head>
<body class="admin">
<aside class="sidebar" aria-label="منوی مدیریت">
    <a class="logo" href="{{ route('admin.dashboard') }}">{{ config('app.name') }}</a>
    <nav>
        <a href="{{ route('admin.dashboard') }}">داشبورد</a>
        <a href="{{ route('admin.media.index') }}">رسانه‌ها</a>
        <a href="{{ route('admin.badges.index') }}">کارت سینه</a>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.settings.edit') }}">تنظیمات سایت</a>
        @endif
        @foreach (\App\Admin\Registry::groups() as $group => $resources)
            @php($visible = array_filter($resources, fn ($r) => ! $r->adminOnly || auth()->user()->isAdmin()))
            @if ($visible)
                <h2>{{ $group }}</h2>
                @foreach ($visible as $r)
                    <a href="{{ route('admin.resource.index', ['resource' => $r->key()]) }}">{{ $r->title() }}</a>
                @endforeach
            @endif
        @endforeach
    </nav>
</aside>
<div class="main">
    <header class="topbar">
        <span>{{ auth()->user()->name }}@if (auth()->user()->position) <small>— {{ auth()->user()->position }}</small>@endif</span>
        <a href="{{ url('/') }}" target="_blank" rel="noopener">مشاهدهٔ سایت</a>
        <form method="post" action="{{ route('admin.logout') }}">@csrf <button class="link" type="submit">خروج</button></form>
    </header>
    <main>
        @if (session('status'))<p class="flash ok" role="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<p class="flash err" role="alert">خطا در اطلاعات واردشده؛ موارد قرمز را اصلاح کنید.</p>@endif
        @yield('content')
    </main>
</div>
<script src="{{ asset('js/admin.js') }}" defer></script>
@if (is_file(public_path('vendor/jodit/jodit.min.js')))
    <script src="{{ asset('vendor/jodit/jodit.min.js') }}" defer></script>
@endif
</body>
</html>
