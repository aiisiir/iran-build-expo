@php
    $messages = [
        403 => ['دسترسی مجاز نیست', 'Access denied'],
        404 => ['صفحه پیدا نشد', 'Page not found'],
        419 => ['نشست منقضی شده است؛ صفحه را دوباره باز کنید', 'Session expired; please reload the page'],
        429 => ['درخواست‌های بیش از حد؛ کمی بعد دوباره تلاش کنید', 'Too many requests; please try again shortly'],
        500 => ['خطای سرور؛ مشکل ثبت شد', 'Server error'],
        503 => ['سرویس موقتاً در دسترس نیست', 'Service temporarily unavailable'],
    ];
    [$fa, $en] = $messages[$code];
@endphp
<!doctype html>
<html lang="fa-IR" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $code }} — Iran Build Expo</title>
    <link rel="stylesheet" href="{{ url('css/site.css') }}">
</head>
<body>
    <main class="hero error-box">
        <p class="eyebrow">{{ $code }}</p>
        <h1 lang="fa" dir="rtl">{{ $fa }}</h1>
        <p lang="en" dir="ltr">{{ $en }}</p>
        <p><a href="{{ url('/fa') }}">صفحهٔ اصلی</a> · <a href="{{ url('/en') }}" lang="en">Home</a></p>
    </main>
</body>
</html>
