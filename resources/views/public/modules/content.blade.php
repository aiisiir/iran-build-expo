<div class="content-block">
    @if ($module->media)<div class="content-media">@include('public.partials.image', ['media' => $module->media])</div>@endif
    @if ($module->body)<div class="prose">{!! \App\Support\HtmlSanitizer::clean($module->body) !!}</div>@endif
</div>
