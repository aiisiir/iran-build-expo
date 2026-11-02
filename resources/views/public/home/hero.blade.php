{{-- $hero: Collection<Post>|null from a "hero" posts module; without it, the four built-in placeholder slides are shown. --}}
@php
    $slides = ($hero ?? null)?->isNotEmpty()
        ? $hero->map(fn ($p, $i) => ['title' => $p->title, 'text' => $p->summary ?: $p->lead, 'media' => $p->image, 'src' => asset('images/hero/slide-'.($i + 1).'.svg'), 'alt' => '', 'url' => $p->link_url ?: \App\Support\PublicUrl::path($p->path())])->all()
        : collect(__('home.slides'))->map(fn ($s, $i) => ['title' => $s['title'], 'text' => $s['text'], 'src' => asset('images/hero/slide-'.($i + 1).'.svg'), 'alt' => $s['alt'], 'url' => null])->all();
    $total = count($slides);
    $code = app('site.language')->code; // the layout's $language is not yet shared while sections render
@endphp
<section class="hero-slider" role="region" aria-roledescription="carousel" aria-label="{{ __('home.hero_label') }}" data-hero>
    <ul class="hero-slides">
        @foreach ($slides as $i => $s)
            <li @class(['hero-slide', 'is-active' => $loop->first]) role="group" aria-roledescription="slide" aria-label="{{ __('home.slide_of', ['n' => $i + 1, 'total' => $total]) }}">
                @isset($s['media'])
                    @include('public.partials.image', ['media' => $s['media'], 'alt' => $s['title'], 'class' => 'hero-image', 'eager' => $loop->first, 'priority' => $loop->first])
                @else
                    <img class="hero-image" src="{{ $s['src'] }}" alt="{{ $s['alt'] }}" width="1600" height="900" decoding="async"
                         @if ($loop->first) loading="eager" fetchpriority="high" @else loading="lazy" @endif>
                @endisset
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
            <button type="button" class="ctl" data-hero-toggle aria-label="{{ __('home.pause') }}" data-label-pause="{{ __('home.pause') }}" data-label-play="{{ __('home.play') }}"><span aria-hidden="true" data-hero-icon>❚❚</span></button>
            <button type="button" class="ctl" data-hero-next aria-label="{{ __('home.next') }}"><span aria-hidden="true">›</span></button>
            <span class="hero-count" aria-hidden="true"><span data-hero-current>{{ \App\Support\Digits::forLocale('1', $code) }}</span> / {{ \App\Support\Digits::forLocale((string) $total, $code) }}</span>
        </div>
    @endif
</section>
