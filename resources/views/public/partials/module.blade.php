<section class="section module module-{{ $module->type->value }}" @if ($module->show_title && $module->title) aria-labelledby="m-{{ $module->id }}" @endif>
    <div class="container">
        @if ($module->show_title && $module->title)<h2 id="m-{{ $module->id }}">{{ $module->title }}</h2>@endif
        @include('public.modules.'.$module->type->value, ['module' => $module])
    </div>
</section>
