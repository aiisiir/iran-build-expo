<!doctype html>
<html lang="{{ app()->getLocale() === 'fa' ? 'fa-IR' : 'en' }}" dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}">
<head>
    @include('components.seo.meta', ['seo' => $seo ?? []])
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body>
    <a class="skip-link" href="#main-content">{{ __('home.skip_to_content') }}</a>
    <header class="site-header">
        <a class="brand" href="{{ route('public.home', ['locale' => app()->getLocale()]) }}">{{ __('home.brand') }}</a>
        <nav aria-label="{{ __('home.language_navigation') }}">
            <a lang="fa" hreflang="fa-IR" href="{{ route('public.home', ['locale' => 'fa']) }}">فارسی</a>
            <a lang="en" hreflang="en" href="{{ route('public.home', ['locale' => 'en']) }}">English</a>
        </nav>
    </header>
    <main id="main-content">
        @yield('content')
    </main>
    <footer class="site-footer">
        <p>{{ __('home.footer_note') }}</p>
    </footer>
</body>
</html>
