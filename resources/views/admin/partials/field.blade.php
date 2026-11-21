@php
    $name = $field->name;
    $raw = $field->loader ? ($field->loader)($model) : $model->getAttribute($name);
    if ($raw instanceof BackedEnum) { $raw = $raw->value; }
    if ($raw instanceof DateTimeInterface) { $raw = $raw->format($field->type === 'date' ? 'Y-m-d' : 'Y-m-d\TH:i'); }
    if ($raw === null && ! $model->exists) { $raw = $field->default; }
    $value = $field->type === 'password' ? '' : old($name, $raw);
@endphp
<div class="field @error($name) has-error @enderror">
    @if ($field->type === 'checkbox')
        <label class="check"><input type="checkbox" name="{{ $name }}" value="1" @checked(session()->hasOldInput() ? old($name) : $raw)> {{ $field->label }}</label>
    @else
        <label for="f-{{ $name }}">{{ $field->label }}@if ($field->isRequired()) <b aria-hidden="true">*</b>@endif</label>
        @switch($field->type)
            @case('textarea')
                <textarea id="f-{{ $name }}" name="{{ $name }}" rows="4">{{ $value }}</textarea> @break
            @case('rich')
                <textarea id="f-{{ $name }}" class="rich" name="{{ $name }}" rows="12">{{ $value }}</textarea> @break
            @case('select')
                <select id="f-{{ $name }}" name="{{ $name }}">
                    @unless ($field->isRequired())<option value="">—</option>@endunless
                    @foreach ($field->choices() as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>{{ $optLabel }}</option>
                    @endforeach
                </select> @break
            @case('multiselect')
                <select id="f-{{ $name }}" name="{{ $name }}[]" multiple size="5">
                    @foreach ($field->choices() as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected(in_array($optValue, (array) $value))>{{ $optLabel }}</option>
                    @endforeach
                </select> @break
            @case('media')
                @php $asset = $value ? \App\Models\MediaAsset::find($value) : null; @endphp
                <div class="media-field" data-media-field data-kind="{{ $field->mediaKind()?->value }}">
                    <input type="hidden" id="f-{{ $name }}" name="{{ $name }}" value="{{ $value }}">
                    <span class="media-preview" data-media-preview>
                        @if ($asset?->kind === \App\Enums\MediaKind::Image)<img src="{{ $asset->url() }}" alt="{{ $asset->alt }}">
                        @elseif ($asset)<span>{{ $asset->original_name }}</span>
                        @else<span class="muted">چیزی انتخاب نشده</span>@endif
                    </span>
                    <button type="button" class="btn ghost" data-media-open>انتخاب یا بارگذاری</button>
                    <button type="button" class="link danger" data-media-clear @if (! $asset) hidden @endif>برداشتن</button>
                </div> @break
            @case('datetime')
                <input id="f-{{ $name }}" type="datetime-local" name="{{ $name }}" value="{{ $value }}" dir="ltr"> @break
            @case('date')
                <input id="f-{{ $name }}" type="date" name="{{ $name }}" value="{{ $value }}" dir="ltr"> @break
            @case('number')
                <input id="f-{{ $name }}" type="number" name="{{ $name }}" value="{{ $value }}" min="0" dir="ltr"> @break
            @case('password')
                <input id="f-{{ $name }}" type="password" name="{{ $name }}" autocomplete="new-password" dir="ltr"> @break
            @case('color')
                <input id="f-{{ $name }}" type="color" name="{{ $name }}" value="{{ $value }}"> @break
            @default
                <input id="f-{{ $name }}" type="text" name="{{ $name }}" value="{{ $value }}">
        @endswitch
    @endif
    @if ($field->help)<small>{{ $field->help }}</small>@endif
    @error($name)<small class="error" role="alert">{{ $message }}</small>@enderror
</div>
