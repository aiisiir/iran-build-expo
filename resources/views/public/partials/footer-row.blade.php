{{-- One row of the footer contact block: icon (the link's CSS class names it), label, optional detail line. --}}
<x-icon :name="$link->css_class ?: 'headset'" /><span>{{ $link->label }}@if ($link->body)<small>{{ strip_tags($link->body) }}</small>@endif</span>
