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
@include('public.partials.lightbox')
@endsection
