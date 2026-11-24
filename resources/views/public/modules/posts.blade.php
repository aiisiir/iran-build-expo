@php($mode = $module->effectiveDisplay())
@if ($themed && $mode === 'slider')
    {{-- Home news carousel: three cards per view on desktop, swipe track on mobile (site.js). --}}
    <div class="news-carousel" data-news aria-roledescription="carousel" aria-label="{{ $module->title }}">
        <button class="carousel-control previous" type="button" data-carousel="previous" aria-label="{{ __('site.prev') }}"><x-icon name="chevron" /></button>
        <div class="news-track">
            @foreach ($module->items as $post)
                @php($href = $post->link_url ?: \App\Support\PublicUrl::path($post->path()))
                <article class="news-card">
                    <a href="{{ $href }}" class="news-image" tabindex="-1" aria-hidden="true">
                        @if ($post->image)@include('public.partials.image', ['media' => $post->image, 'alt' => ''])@endif
                        @if ($post->published_at)<span class="news-date"><x-icon name="calendar" />{{ \App\Support\DateFormat::show($post->published_at, app('site.language')) }}</span>@endif
                    </a>
                    <div class="news-body">
                        @if ($post->category)<span class="news-category"><x-icon name="layers" />{{ $post->category->name }}</span>@endif
                        <h3><a href="{{ $href }}">{{ $post->title }}</a></h3>
                        @if ($post->summary || $post->lead)<p>{{ $post->summary ?: $post->lead }}</p>@endif
                        <a class="news-read" href="{{ $href }}"><x-icon name="chevron" />{{ __('site.read_story') }}<span class="sr-only"> — {{ $post->title }}</span></a>
                    </div>
                </article>
            @endforeach
        </div>
        <button class="carousel-control next" type="button" data-carousel="next" aria-label="{{ __('site.next') }}"><x-icon name="chevron" /></button>
    </div>
    @if ($module->items->count() > 1)
        <div class="carousel-dots">@foreach ($module->items as $post)<button type="button" data-slide="{{ $loop->index }}" aria-label="{{ $post->title }}" @if ($loop->first) aria-current="true" @endif></button>@endforeach</div>
    @endif
    <span class="sr-only news-update" aria-live="polite"></span>
    @if ($module->source_page_id && $module->sourcePage)
        <a class="button button--outline news-all" href="{{ \App\Support\PublicUrl::path($module->sourcePage->path()) }}"><x-icon name="external" />{{ __('site.view_all') }}</a>
    @endif
@elseif ($mode === 'slider')
    <div class="slider" data-slider>
        <ul class="slides">
            @foreach ($module->items as $post)
                @php($href = $post->link_url ?: \App\Support\PublicUrl::path($post->path()))
                <li class="slide">
                    <a href="{{ $href }}">
                        @if ($post->image)@include('public.partials.image', ['media' => $post->image, 'alt' => $post->title, 'eager' => $loop->first])@endif
                        <span class="slide-title">{{ $post->title }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <button type="button" class="slider-btn prev" data-slide-prev aria-label="{{ __('site.prev') }}" hidden>‹</button>
        <button type="button" class="slider-btn next" data-slide-next aria-label="{{ __('site.next') }}" hidden>›</button>
    </div>
@else
    <div class="cards {{ $mode === 'list' ? 'is-list' : 'is-grid' }}">
        @foreach ($module->items as $post)@include('public.partials.post-card', ['post' => $post])@endforeach
    </div>
@endif
@if (! $themed && $module->source_page_id && $module->sourcePage)
    <p class="view-all"><a href="{{ \App\Support\PublicUrl::path($module->sourcePage->path()) }}">{{ __('site.view_all') }}</a></p>
@endif
