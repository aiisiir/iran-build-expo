{{-- $hero: posts of the page's "hero" module (title = bold line, lead = light line, summary = text, source name = button label, link = button target).
     $utility: modules shown in the strip that overlaps the hero's bottom edge (links = quick actions, content with image = partner logos). --}}
@if (($hero ?? null)?->isNotEmpty())
@php($total = $hero->count())
<section class="hero" role="region" aria-roledescription="carousel" aria-label="{{ __('home.hero_label') }}" data-hero>
    <ul class="hero-slides">
        @foreach ($hero as $post)
            @php($url = $post->link_url ?: \App\Support\PublicUrl::path($post->path()))
            <li @class(['hero-slide', 'is-active' => $loop->first]) role="group" aria-roledescription="slide" aria-label="{{ __('home.slide_of', ['n' => $loop->iteration, 'total' => $total]) }}">
                @if ($post->image)@include('public.partials.image', ['media' => $post->image, 'alt' => $post->image->alt ?: '', 'class' => 'hero-photo', 'eager' => $loop->first, 'priority' => $loop->first])@endif
                <div class="hero-blue" aria-hidden="true"></div><div class="hero-fade" aria-hidden="true"></div>
                <div class="container hero-inner">
                    <div class="hero-copy">
                        <h2 class="hero-title"><strong>{{ $post->title }}</strong>@if ($post->lead)<span>{{ $post->lead }}</span>@endif</h2>
                        @if ($post->summary)<p>{{ $post->summary }}</p>@endif
                        @if ($url)<a class="button button--green hero-cta" href="{{ $url }}">{{ $post->source_name ?: __('site.read_more') }}<span class="sr-only"> — {{ $post->title }}</span></a>@endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
    @if ($total > 1)
        <div class="hero-controls" data-hero-controls hidden>
            <button type="button" class="ctl" data-hero-prev aria-label="{{ __('home.prev') }}"><span aria-hidden="true">‹</span></button>
            <button type="button" class="ctl" data-hero-next aria-label="{{ __('home.next') }}"><span aria-hidden="true">›</span></button>
        </div>
    @endif
    @if (($utility ?? collect())->isNotEmpty())
        <div class="container hero-utility">
            @foreach ($utility as $module)
                @if ($module->isOrganizers())
                    {{-- One logo per link; the pill grows or shrinks with the number and size of the logos. --}}
                    <div class="partners" role="group" @if ($module->title) aria-label="{{ $module->title }}" @endif>
                        @foreach ($module->items as $link)
                            @continue(! $link->iconMedia)
                            @php($href = $link->href())
                            @if ($href)<a class="partner" href="{{ $href }}" title="{{ $link->label }}" @if ($link->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>@else<span class="partner" title="{{ $link->label }}">@endif
                                @include('public.partials.image', ['media' => $link->iconMedia, 'alt' => $link->label])
                            @if ($href)</a>@else</span>@endif
                        @endforeach
                    </div>
                @elseif ($module->type->value === 'links')
                    <div class="quick-actions" role="group" @if ($module->title) aria-label="{{ $module->title }}" @endif>
                        @foreach ($module->items as $link)
                            @if ($href = $link->href())
                                <a href="{{ $href }}" @if ($link->target === '_blank') target="_blank" rel="noopener noreferrer" @endif><span>@if ($link->iconMedia)@include('public.partials.image', ['media' => $link->iconMedia, 'class' => 'link-icon', 'alt' => ''])@endif</span><strong>{{ $link->label }}</strong></a>
                            @endif
                        @endforeach
                    </div>
                @elseif ($module->media)
                    <div class="partners">@include('public.partials.image', ['media' => $module->media, 'alt' => $module->media->alt ?: $module->title])</div>
                @endif
            @endforeach
        </div>
    @endif
</section>
@endif
