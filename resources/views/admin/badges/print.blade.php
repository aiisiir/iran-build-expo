<!doctype html>
<html lang="fa-IR" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex,nofollow">
    <title>چاپ کارت سینه</title>
    <link rel="stylesheet" href="{{ asset('css/badge-print.css') }}">
</head>
<body>
<p class="no-print"><button type="button" data-print>چاپ</button> — اندازهٔ کارت ۶۰ × ۱۵۰ میلی‌متر، بدون حاشیه (در پنجرهٔ چاپ مقیاس را «۱۰۰٪» بگذارید).</p>
@php($site = \App\Support\SiteSettings::for(\App\Models\Language::where('code', 'fa')->value('id') ?? 0))
@foreach ($badges as $b)
    <article class="badge" style="--accent: {{ $b->type->color }}">
        <header>
            <img src="{{ asset('images/logo.svg') }}" alt="" width="48" height="48">
            <strong>{{ $site['site_name'] ?? config('app.name') }}</strong>
            <span class="en" dir="ltr">{{ config('app.name') }}</span>
        </header>
        <p class="edition">{{ $b->edition->name }}</p>
        <div class="photo-wrap"><img class="photo" src="{{ route('admin.badges.photo', $b->id) }}" alt="" width="360" height="450"></div>
        <h1>{{ $b->full_name }}</h1>
        <p class="role">{{ $b->type->name }}</p>
        <footer dir="ltr">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</footer>
    </article>
@endforeach
<script src="{{ asset('js/admin.js') }}" defer></script>
</body>
</html>
