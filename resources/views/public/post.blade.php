@extends('layouts.app')
@section('content')
@include('public.partials.breadcrumbs')
<article class="container article">
    <header>
        <h1>{{ $post->title }}</h1>
        @if ($post->lead)<p class="lead">{{ $post->lead }}</p>@endif
        <p class="meta">
            @if ($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ __('site.published') }}: {{ \App\Support\DateFormat::show($post->published_at, $language) }}</time>@endif
            @if ($post->source_name) · {{ __('site.source') }}: {{ $post->source_name }}@endif
        </p>
    </header>
    @if ($post->image)<figure class="article-image">@include('public.partials.image', ['media' => $post->image, 'eager' => true])</figure>@endif
    @if ($post->body)<div class="prose">{!! \App\Support\HtmlSanitizer::clean($post->body) !!}</div>@endif
</article>
@endsection
