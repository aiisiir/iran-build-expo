@if ($tourUrl)
    <div class="embed">
        <iframe src="{{ $tourUrl }}" title="{{ $module->title ?: 'Virtual tour' }}" loading="lazy" allowfullscreen
                sandbox="allow-scripts allow-same-origin allow-fullscreen" referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </div>
@else
    <p>{{ __('site.tour_unavailable') }}</p>
@endif
