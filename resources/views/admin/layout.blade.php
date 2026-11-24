<!doctype html>
<html lang="fa-IR" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل مدیریت') — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ url('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @if (is_file(public_path('vendor/jodit/jodit.min.css')))
        <link rel="stylesheet" href="{{ asset('vendor/jodit/jodit.min.css') }}">
    @endif
</head>
<body class="admin">
<aside class="sidebar" id="sidebar" aria-label="منوی مدیریت">
    <a class="logo" href="{{ route('admin.dashboard') }}">{{ config('app.name') }}</a>
    <nav>
        <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>داشبورد</a>
        @php
            $isAdmin = auth()->user()->isAdmin();
            // Extra (non-Resource) entries, appended to a group.
            $extra = [
                'مدیریت داده‌ها' => [['رسانه‌ها', 'admin.media.*', route('admin.media.index')], ['کارت سینه', 'admin.badges.*', route('admin.badges.index')]],
                'سیستم' => array_merge($isAdmin ? [['تنظیمات سایت', 'admin.settings.*', route('admin.settings.edit')]] : [], [['رکوردهای حذف‌شده', 'admin.trash', route('admin.trash')]]),
            ];
        @endphp
        @foreach (\App\Admin\Registry::groups() as $group => $resources)
            <h2>{{ $group }}</h2>
            @foreach (array_filter($resources, fn ($r) => $r->allows(auth()->user())) as $r)
                <a href="{{ route('admin.resource.index', ['resource' => $r->key()]) }}" @if (request()->route('resource')?->key() === $r->key()) aria-current="page" @endif>{{ $r->title() }}</a>
            @endforeach
            @foreach ($extra[$group] ?? [] as [$label, $pattern, $url])
                <a href="{{ $url }}" @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        @endforeach
    </nav>
</aside>
<div class="nav-backdrop" data-nav-close hidden></div>
<div class="main">
    <header class="topbar">
        <button class="nav-toggle" type="button" aria-controls="sidebar" aria-expanded="false" data-nav-toggle><span aria-hidden="true">☰</span><span class="sr-only">منو</span></button>
        <span class="who">{{ auth()->user()->name }}@if (auth()->user()->position) <small>— {{ auth()->user()->position }}</small>@endif</span>
        <a href="{{ url('/') }}" target="_blank" rel="noopener">مشاهدهٔ سایت</a>
        <form method="post" action="{{ route('admin.logout') }}">@csrf <button class="link" type="submit">خروج</button></form>
    </header>
    <main>
        @if (session('status'))<p class="flash ok" role="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<p class="flash err" role="alert">خطا در اطلاعات واردشده؛ موارد قرمز را اصلاح کنید.</p>@endif
        @yield('content')
    </main>
</div>
@include('admin.partials.media-picker')
<script src="{{ asset('js/admin.js') }}" defer></script>
@if (is_file(public_path('vendor/jodit/jodit.min.js')))
    <script src="{{ asset('vendor/jodit/jodit.min.js') }}" defer></script>
@endif
</body>
</html>
