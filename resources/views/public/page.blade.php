@extends('layouts.app')
@section('content')
@unless ($page->is_home)@include('public.partials.breadcrumbs')@endunless
<article @class(['home' => $page->is_home])>
    @if ($page->is_home)
        <h1 class="sr-only">{{ $page->title }}</h1>
        @include('public.home.hero', ['hero' => $hero ?? null, 'utility' => $utility ?? collect()])
    @else
        <header class="page-head container"><h1>{{ $page->title }}</h1></header>
    @endif
    @if ($page->body)<div class="container prose">{!! \App\Support\HtmlSanitizer::clean($page->body) !!}</div>@endif
    @foreach ($modules as $module)@include('public.partials.module', ['module' => $module])@endforeach
</article>
@endsection
