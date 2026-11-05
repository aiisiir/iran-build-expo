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
@foreach ($badges as $b)
    <article class="badge" style="--accent: {{ $b->type->color }}">
        <header><img src="{{ asset('images/logo.svg') }}" alt="" width="40" height="40"><span>{{ $b->edition->name }}</span></header>
        <img class="photo" src="{{ route('admin.badges.photo', $b->id) }}" alt="" width="360" height="450">
        <h1>{{ $b->full_name }}</h1>
        <p class="role">{{ $b->type->name }}</p>
        <footer>{{ config('app.name') }}</footer>
    </article>
@endforeach
<script src="{{ asset('js/admin.js') }}" defer></script>
</body>
</html>
