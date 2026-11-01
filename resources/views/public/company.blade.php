@extends('layouts.app')
@section('content')
@include('public.partials.breadcrumbs')
<article class="container article">
    <header class="company-head">
        @if ($company->logo)@include('public.partials.image', ['media' => $company->logo, 'alt' => $company->name, 'eager' => true, 'class' => 'company-logo'])@endif
        <div><h1>{{ $company->name }}</h1>@if ($company->industry)<span class="badge-industry">{{ $company->industry }}</span>@endif</div>
    </header>
    @if ($company->description)<div class="prose">{!! \App\Support\HtmlSanitizer::clean($company->description) !!}</div>@endif
    @if ($company->products->isNotEmpty())
        <section aria-labelledby="products-title">
            <h2 id="products-title">{{ __('site.products') }}</h2>
            <ul class="cards is-grid">
                @foreach ($company->products as $product)
                    @php($cover = $product->images->first()?->media)
                    <li class="card">
                        @if ($cover)<a class="card-media" href="{{ \App\Support\PublicUrl::path($product->path()) }}" tabindex="-1" aria-hidden="true">@include('public.partials.image', ['media' => $cover, 'alt' => ''])</a>@endif
                        <div class="card-body"><h3><a href="{{ \App\Support\PublicUrl::path($product->path()) }}">{{ $product->name }}</a></h3></div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</article>
@endsection
