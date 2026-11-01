<!doctype html>
<html lang="{{ $language->htmlLang() }}" dir="{{ $language->direction }}">
<head>
    <meta charset="utf-8">
    @include('components.seo.meta', ['seo' => $seo ?? []])
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ url('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <script src="{{ asset('js/site.js') }}" defer></script>
</head>
<body class="lang-{{ $language->code }}">
    <a class="skip-link" href="#main-content">{{ __('site.skip') }}</a>
    <header class="site-header">
        <div class="container header-row">
            @php($brandName = $logoLink?->label ?: ($site['site_name'] ?? config('app.name')))
            <a class="brand" href="{{ url('/'.$language->code) }}">
                @if ($logoLink?->iconMedia)
                    @include('public.partials.image', ['media' => $logoLink->iconMedia, 'class' => 'brand-logo', 'eager' => true, 'alt' => ''])
                @else
                    <img class="brand-logo" src="{{ asset('images/logo.svg') }}" alt="" width="40" height="40">
                @endif
                <span @class(['sr-only' => $logoLink && ! $logoLink->show_label])>{{ $brandName }}</span>
            </a>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle>{{ __('site.menu') }}</button>
            <nav id="primary-nav" class="primary-nav" aria-label="{{ __('site.main_nav') }}">
                @include('public.partials.menu', ['byParent' => $menuLinks, 'parent' => 0, 'depth' => 0])
            </nav>
            @if ($switcher->isNotEmpty())
                <nav class="lang-switch" aria-label="{{ __('site.languages') }}">
                    @foreach ($switcher as $item)
                        <a lang="{{ $item['language']->code }}" hreflang="{{ $item['language']->htmlLang() }}" href="{{ $item['url'] }}">{{ $item['language']->native_name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
    </header>
    <main id="main-content">
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <strong>{{ $site['site_name'] ?? config('app.name') }}</strong>
                @foreach (['footer_about', 'footer_dates', 'footer_venue', 'footer_address'] as $key)
                    @isset($site[$key])<p>{{ $site[$key] }}</p>@endisset
                @endforeach
                @isset($site['footer_phone'])<p><a href="tel:{{ preg_replace('/[^\d+]/', '', $site['footer_phone']) }}" dir="ltr">{{ $site['footer_phone'] }}</a></p>@endisset
                @isset($site['footer_email'])<p><a href="mailto:{{ $site['footer_email'] }}">{{ $site['footer_email'] }}</a></p>@endisset
            </div>
            @foreach ($footerGroups as $entry)
                <nav aria-label="{{ $entry['group']->title ?: __('site.footer_nav') }}">
                    @if ($entry['group']->show_title && $entry['group']->title)<strong>{{ $entry['group']->title }}</strong>@endif
                    @include('public.partials.menu', ['byParent' => $entry['links'], 'parent' => 0, 'depth' => 0])
                </nav>
            @endforeach
        </div>
        <p class="container copyright">{{ $site['copyright'] ?? '© '.date('Y').' '.config('app.name') }}</p>
    </footer>
</body>
</html>
