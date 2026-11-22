@php($href = $link->href())
@if ($href)
    <a href="{{ $href }}" @if ($link->css_class) class="{{ $link->css_class }}" @endif
       @if (rawurldecode((string) parse_url($href, PHP_URL_PATH)) === '/'.rawurldecode(request()->path())) aria-current="page" @endif
       @if ($link->target === '_blank') target="_blank" rel="noopener noreferrer" @endif
       @if ($link->title_attr) title="{{ $link->title_attr }}" @endif>
        @if (! empty($chevron))<x-icon name="chevron" />@endif
        @if ($link->iconMedia)@include('public.partials.image', ['media' => $link->iconMedia, 'class' => 'link-icon', 'alt' => ''])@endif
        <span @class(['sr-only' => ! $link->show_label])>{{ $link->label }}</span>
    </a>
@else
    <span>{{ $link->label }}</span>
@endif
@if ($link->body){!! \App\Support\HtmlSanitizer::clean($link->body) !!}@endif
