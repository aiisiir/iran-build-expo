{{-- Rich text block: optional picture, then the editor's HTML (sanitized on save and on output). --}}
<div class="rich-text">
    @if ($module->media)@include('public.partials.image', ['media' => $module->media, 'alt' => $module->media->alt ?: $module->title])@endif
    @if ($module->body){!! \App\Support\HtmlSanitizer::clean($module->body) !!}@endif
</div>
