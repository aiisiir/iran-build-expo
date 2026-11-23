{{-- A module renders as a plain section, or as a themed home section when its CSS class names one (news-section, resources-section, registration-section, gallery-section). --}}
@php
    $classes = explode(' ', (string) $module->css_class);
    $isAbout = $module->type->value === 'about';
    $themed = $isAbout || collect($classes)->contains(fn ($c) => str_ends_with($c, '-section'));
    $showTitle = $module->show_title && $module->title;
    $intro = $module->type->value === 'content' ? null : \App\Support\HtmlSanitizer::clean($module->body);
@endphp
<section @if ($module->css_id) id="{{ $module->css_id }}" @endif class="{{ $themed ? '' : 'section module module-'.$module->type->value }} {{ $module->css_class }} {{ $isAbout && ! in_array('about-section', $classes, true) ? 'about-section' : '' }}" @if ($showTitle) aria-labelledby="m-{{ $module->id }}" @endif>
    @if (in_array('resources-section', $classes, true))<div class="resources-backdrop" aria-hidden="true"></div>@endif
    @if (in_array('registration-section', $classes, true))<div class="registration-wave wave-one" aria-hidden="true"></div><div class="registration-wave wave-two" aria-hidden="true"></div>@endif
    <div @class(['container', 'resources-inner' => in_array('resources-section', $classes, true), 'registration-panel' => in_array('registration-section', $classes, true), 'about-panel' => $isAbout])>
        @if ($themed && ! $isAbout && ! in_array('registration-section', $classes, true))
            <header @class(['section-heading', 'light' => in_array('resources-section', $classes, true)])>
                @if ($showTitle)<h2 id="m-{{ $module->id }}">{{ $module->title }}</h2>@endif
                @if ($intro)<div class="section-intro">{!! $intro !!}</div>@endif
            </header>
        @elseif ($showTitle && ! $themed)
            <h2 id="m-{{ $module->id }}">{{ $module->title }}</h2>
        @endif
        @include('public.modules.'.$module->type->value, ['module' => $module, 'themed' => $themed, 'classes' => $classes])
    </div>
</section>
