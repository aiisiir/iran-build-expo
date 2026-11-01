@php($mode = $module->effectiveDisplay())
@if ($mode === 'slider')
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
@if ($module->source_page_id && $module->sourcePage)
    <p class="view-all"><a href="{{ \App\Support\PublicUrl::path($module->sourcePage->path()) }}">{{ __('site.view_all') }}</a></p>
@endif
