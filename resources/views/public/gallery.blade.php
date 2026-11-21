@extends('layouts.app')
@section('content')
@include('public.partials.breadcrumbs')
<article class="container">
    <header class="page-head"><h1>{{ $gallery->title }}</h1>@if ($gallery->description)<p class="lead">{{ $gallery->description }}</p>@endif</header>
    <ul class="gallery gallery-page" data-lightbox>
        @foreach ($items as $item)
            {{-- Without JavaScript the link simply opens the full image. --}}
            <li><figure><a href="{{ $item->media->url() }}" data-caption="{{ $item->caption }}">@include('public.partials.image', ['media' => $item->media])</a>@if ($item->caption)<figcaption>{{ $item->caption }}</figcaption>@endif</figure></li>
        @endforeach
    </ul>
</article>
<dialog class="lightbox" aria-label="{{ $gallery->title }}" hidden>
    <button type="button" class="lb-close" data-lb-close aria-label="{{ __('site.close') }}">✕</button>
    <button type="button" class="lb-nav lb-prev" data-lb-prev aria-label="{{ __('site.previous') }}">‹</button>
    <figure><img alt=""><figcaption></figcaption></figure>
    <button type="button" class="lb-nav lb-next" data-lb-next aria-label="{{ __('site.next') }}">›</button>
    <span class="lb-count" aria-live="polite"></span>
</dialog>
@endsection
