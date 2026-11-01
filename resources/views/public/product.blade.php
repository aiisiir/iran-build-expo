@extends('layouts.app')
@section('content')
@include('public.partials.breadcrumbs')
<article class="container article">
    <h1>{{ $product->name }}</h1>
    <p class="meta"><a href="{{ \App\Support\PublicUrl::path($company->path()) }}">{{ $company->name }}</a></p>
    @if ($product->images->isNotEmpty())
        <ul class="gallery">@foreach ($product->images as $image)<li>@include('public.partials.image', ['media' => $image->media, 'eager' => $loop->first])</li>@endforeach</ul>
    @endif
    @if ($product->description)<div class="prose">{!! \App\Support\HtmlSanitizer::clean($product->description) !!}</div>@endif
</article>
@endsection
