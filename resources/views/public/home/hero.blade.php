{{-- $hero: Collection<Post> from the page's "hero" posts module; nothing is shown without it. --}}
@if (($hero ?? null)?->isNotEmpty())
@php
    $slides = $hero->map(fn ($p) => ['title' => $p->title, 'text' => $p->summary ?: $p->lead, 'media' => $p->image, 'url' => $p->link_url ?: \App\Support\PublicUrl::path($p->path())])->all();
    $total = count($slides);
    $code = app('site.language')->code; // the layout's $language is not yet shared while sections render
@endphp
<section class="hero-slider" role="region" aria-roledescription="carousel" aria-label="{{ __('home.hero_label') }}" data-hero>
    <ul class="hero-slides">
        @foreach ($slides as $i => $s)
            <li @class(['hero-slide', 'is-active' => $loop->first]) role="group" aria-roledescription="slide" aria-label="{{ __('home.slide_of', ['n' => $i + 1, 'total' => $total]) }}">
                @if ($s['media'])@include('public.partials.image', ['media' => $s['media'], 'alt' => $s['title'], 'class' => 'hero-image', 'eager' => $loop->first, 'priority' => $loop->first])@endif
                <div class="container hero-copy-wrap">
                    <div class="hero-copy">
                        <h2>{{ $s['title'] }}</h2>
                        @if ($s['text'])<p>{{ $s['text'] }}</p>@endif
                        @if ($s['url'])<a class="btn btn-gold" href="{{ $s['url'] }}">{{ __('site.read_more') }}<span class="sr-only"> — {{ $s['title'] }}</span></a>@endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
    @if ($total > 1)
        <div class="hero-controls" data-hero-controls hidden>
            <button type="button" class="ctl" data-hero-prev aria-label="{{ __('home.prev') }}"><span aria-hidden="true">‹</span></button>
            <button type="button" class="ctl" data-hero-next aria-label="{{ __('home.next') }}"><span aria-hidden="true">›</span></button>
            <span class="hero-count" aria-hidden="true"><span data-hero-current>{{ \App\Support\Digits::forLocale('1', $code) }}</span> / {{ \App\Support\Digits::forLocale((string) $total, $code) }}</span>
        </div>
    @endif
</section>
@endif
