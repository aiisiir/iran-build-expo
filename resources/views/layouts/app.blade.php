<!doctype html>
<html lang="{{ $language->htmlLang() }}" dir="{{ $language->direction }}">
<head>
    <meta charset="utf-8">
    @include('components.seo.meta', ['seo' => $seo ?? []])
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ url('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ url('favicon.ico') }}" sizes="48x48">
    <link rel="apple-touch-icon" href="{{ url('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#245e9f">
    <link rel="preload" href="{{ asset('fonts/Vazirmatn-Variable.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    @php($custom = \App\Models\CustomSnippet::bundles())
    @if ($custom['css'])<link rel="stylesheet" href="{{ route('custom.asset', 'css') }}?v={{ $custom['css']['version'] }}">@endif
    <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
    @if ($custom['js'])<script src="{{ route('custom.asset', 'js') }}?v={{ $custom['js']['version'] }}" defer></script>@endif
</head>
<body class="lang-{{ $language->code }}">
    @php($siteName = $site['site_name'] ?? 'Benis Fairs')
    <a class="skip-link" href="#main-content">{{ __('site.skip') }}</a>
    <header class="site-header">
        <div class="container masthead">
            @php($brandName = $logoLink?->label ?: ($siteName))
            <a class="brand" href="{{ url('/'.$language->code) }}">
                <span class="brand-mark">
                    @if ($logoLink?->iconMedia)@include('public.partials.image', ['media' => $logoLink->iconMedia, 'class' => 'brand-logo', 'eager' => true, 'alt' => $brandName])@endif
                    @isset($site['brand_caption'])<small lang="en" aria-hidden="true">{{ $site['brand_caption'] }}</small>@endisset
                </span>
                <span class="brand-copy">
                    <span>{{ $siteName }}</span>
                    @isset($site['tagline'])<strong>{{ $site['tagline'] }}</strong>@endisset
                </span>
            </a>
            @if ($edition)
                <div class="header-contact header-edition">
                    <x-icon name="calendar" />
                    <span><strong>{{ $edition->title($language) }}</strong>@if ($edition->schedule($language))<small>{{ $edition->schedule($language) }}</small>@endif</span>
                </div>
            @endif
            <button class="menu-toggle icon-button" type="button" aria-expanded="false" aria-controls="primary-nav" data-nav-toggle><x-icon name="menu" /><span class="sr-only">{{ __('site.menu') }}</span></button>
        </div>
        <nav id="primary-nav" class="container primary-nav" aria-label="{{ __('site.main_nav') }}">
            @include('public.partials.menu', ['byParent' => $menuLinks, 'parent' => 0, 'depth' => 0])
            @if ($switcher->isNotEmpty())
                <nav class="lang-switch" aria-label="{{ __('site.languages') }}">
                    @foreach ($switcher as $item)
                        <a class="language-link" lang="{{ $item['language']->code }}" hreflang="{{ $item['language']->htmlLang() }}" href="{{ $item['url'] }}">{{ $item['language']->native_name }}</a>
                    @endforeach
                </nav>
            @endif
        </nav>
    </header>
    <main id="main-content">
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-contact">
                <h2><x-icon name="building" />{{ $footerContact['group']->title ?? $siteName }}</h2>
                @isset($site['footer_about'])<p>{{ $site['footer_about'] }}</p>@endisset
                @foreach (($footerContact['links'] ?? collect())->get(0, collect()) as $link)
                    @php($href = $link->href())
                    @if ($href)<a href="{{ $href }}">@include('public.partials.footer-row')</a>@else<div class="footer-line">@include('public.partials.footer-row')</div>@endif
                @endforeach
            </div>
            @foreach ($footerGroups as $entry)
                <nav class="footer-links" aria-label="{{ $entry['group']->title ?: __('site.footer_nav') }}">
                    @if ($entry['group']->show_title && $entry['group']->title)<h2><x-icon :name="$loop->first ? 'globe' : 'layers'" />{{ $entry['group']->title }}</h2>@endif
                    <ul>
                        @foreach ($entry['links']->get(0, collect()) as $link)<li>@include('public.partials.link', ['link' => $link, 'chevron' => true])</li>@endforeach
                    </ul>
                </nav>
            @endforeach
            <div class="footer-brand">
                <a href="{{ url('/'.$language->code) }}">
                    <span class="brand-mark"><img src="{{ asset('brand/benis-wordmark-white.svg') }}" alt="" width="112" height="36">@isset($site['brand_caption'])<small lang="en" aria-hidden="true">{{ $site['brand_caption'] }}</small>@endisset</span>
                    @isset($site['brand_latin'])<strong lang="en">{{ mb_strtoupper($site['brand_latin']) }}</strong>@endisset
                </a>
                <p>{{ $siteName }}</p>
                <div class="footer-building" aria-hidden="true"></div>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>{{ $site['copyright'] ?? '© '.date('Y').' '.$siteName }}</span>
            <div class="footer-bottom-links">
                @isset($site['footer_email'])<a href="mailto:{{ $site['footer_email'] }}" aria-label="{{ $site['footer_email'] }}"><x-icon name="mail" /></a>@endisset
                @foreach ($switcher as $item)<a lang="{{ $item['language']->code }}" hreflang="{{ $item['language']->htmlLang() }}" href="{{ $item['url'] }}">{{ mb_strtoupper($item['language']->code) }}</a>@endforeach
            </div>
        </div>
    </footer>
</body>
</html>
