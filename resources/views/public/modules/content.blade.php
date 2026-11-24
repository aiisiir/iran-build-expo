@if (in_array('registration-section', $classes, true))
    {{-- Banner: title = h2, body = <h3> subtitle, <h4> highlight, three <p> (date pill, note, link button); image = visitor card. --}}
    <div class="registration-copy">
        <span class="registration-icon"><x-icon name="users" /></span>
        @if ($module->show_title && $module->title)<h2 id="m-{{ $module->id }}">{{ $module->title }}</h2>@endif
        @if ($module->body){!! \App\Support\HtmlSanitizer::clean($module->body) !!}@endif
    </div>
    @if ($module->media)<div class="registration-art">@include('public.partials.image', ['media' => $module->media, 'alt' => $module->media->alt ?: $module->title])</div>@endif
@else
    <div class="content-block">
        @if ($module->media)<div class="content-media">@include('public.partials.image', ['media' => $module->media])</div>@endif
        @if ($module->body)<div class="prose">{!! \App\Support\HtmlSanitizer::clean($module->body) !!}</div>@endif
    </div>
@endif
