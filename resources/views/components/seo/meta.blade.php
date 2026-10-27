@php
    $title = $seo['title'] ?? config('app.name');
    $description = $seo['description'] ?? '';
    $canonical = $seo['canonical'] ?? url()->current();
    $alternates = $seo['alternates'] ?? [];
    $robots = $seo['robots'] ?? (app()->environment('production') ? 'index,follow' : 'noindex,nofollow');
    $type = $seo['type'] ?? 'website';
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
<meta name="twitter:card" content="summary">
@if (!empty($seo['image']))
    <meta property="og:image" content="{{ $seo['image'] }}">
@endif
