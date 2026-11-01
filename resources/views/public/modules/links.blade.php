@php($mode = $module->effectiveDisplay())
<ul @class(['link-list', 'slides' => $mode === 'slider', 'is-grid' => $mode !== 'list'])>
    @foreach ($module->items as $link)<li>@include('public.partials.link', ['link' => $link])</li>@endforeach
</ul>
