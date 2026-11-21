<section @if ($module->css_id) id="{{ $module->css_id }}" @endif class="section module module-{{ $module->type->value }} {{ $module->css_class }}" @if ($module->show_title && $module->title) aria-labelledby="m-{{ $module->id }}" @endif>
    <div class="container">
        @if ($module->show_title && $module->title)<h2 id="m-{{ $module->id }}">{{ $module->title }}</h2>@endif
        @include('public.modules.'.$module->type->value, ['module' => $module])
    </div>
</section>
