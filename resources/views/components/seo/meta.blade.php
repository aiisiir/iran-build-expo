@php
    $title = $seo['title'] ?? config('app.name');
    $description = $seo['description'] ?? '';
    $canonical = $seo['canonical'] ?? url()->current();
    $alternates = $seo['alternates'] ?? [];
    $robots = $seo['robots'] ?? (app()->environment('production') ? 'index,follow' : 'noindex,nofollow');
    $type = $seo['type'] ?? 'website';
    // Placeholder brand image; replace public/images/og-default.png (1200x630) before launch.
    $image = $seo['image'] ?? url('images/og-default.png');
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">
@foreach ($alternates as $language => $url)
    <link rel="alternate" hreflang="{{ $language }}" href="{{ $url }}">
@endforeach
@if (!empty($seo['x_default']))
    <link rel="alternate" hreflang="x-default" href="{{ $seo['x_default'] }}">
@endif
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'fa' ? 'fa_IR' : 'en_US' }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
@if (!empty($seo['jsonld']))
    <script type="application/ld+json">{!! json_encode($seo['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endif
