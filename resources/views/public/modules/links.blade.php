@php($mode = $module->effectiveDisplay())
@if (in_array('resources-section', $classes, true))
    {{-- Document cards: label = title, body = description, icon image = preview, link target = download, "title" attribute = button text, CSS class = header icon name (image, map, users…). --}}
    <div class="resource-grid">
        @foreach ($module->items as $link)
            @php($href = $link->href())
            <article class="resource-card">
                <x-icon :name="$link->css_class ?: 'file'" class="resource-icon" />
                <h3>{{ $link->label }}</h3>
                @if ($link->body){!! \App\Support\HtmlSanitizer::clean($link->body) !!}@endif
                <div class="document-preview">@if ($link->iconMedia)@include('public.partials.image', ['media' => $link->iconMedia, 'alt' => $link->label])@endif</div>
                @if ($href)<a class="resource-download" href="{{ $href }}" download @if ($link->target === '_blank') target="_blank" rel="noopener noreferrer" @endif><x-icon name="download" />{{ $link->title_attr ?: __('site.download') }}</a>@endif
            </article>
        @endforeach
    </div>
@elseif (in_array('supporters-section', $classes, true))
    <ul class="supporters-grid">
        @foreach ($module->items as $link)
            @php($href = $link->href())
            <li class="supporter">
                @if ($href)<a href="{{ $href }}" @if ($link->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>@else<div>@endif
                    <span class="supporter-logo">@if ($link->iconMedia)@include('public.partials.image', ['media' => $link->iconMedia, 'alt' => ''])@endif</span>
                    <span class="supporter-name">{{ $link->label }}</span>
                @if ($href)</a>@else</div>@endif
            </li>
        @endforeach
    </ul>
@else
    <ul @class(['link-list', 'slides' => $mode === 'slider', 'is-grid' => $mode !== 'list'])>
        @foreach ($module->items as $link)<li>@include('public.partials.link', ['link' => $link])</li>@endforeach
    </ul>
@endif
