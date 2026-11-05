@extends('admin.layout')
@section('title', 'تنظیمات سایت')
@section('content')
<h1>تنظیمات سایت</h1>
<form class="form" method="post" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')
    @foreach ($languages as $language)
        <fieldset>
            <legend>{{ $language->native_name }}</legend>
            @foreach ($keys as $key => $label)
                <div class="field"><label for="s-{{ $language->id }}-{{ $key }}">{{ $label }}</label>
                    <input id="s-{{ $language->id }}-{{ $key }}" type="text" name="settings[{{ $language->id }}][{{ $key }}]" value="{{ old("settings.{$language->id}.{$key}", $values[$language->id][$key] ?? '') }}" maxlength="2000"></div>
            @endforeach
        </fieldset>
    @endforeach
    <button class="btn" type="submit">ذخیره</button>
</form>
@endsection
