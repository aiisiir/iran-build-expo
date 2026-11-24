@extends('admin.layout')
@section('title', 'تنظیمات سایت')
@section('content')
<h1>تنظیمات سایت</h1>
<nav class="tabs" aria-label="زبان">
    @foreach ($languages as $language)
        <a href="{{ route('admin.settings.edit', ['lang' => $language->code]) }}" @if ($language->id === $current->id) aria-current="page" @endif>{{ $language->native_name }}</a>
    @endforeach
</nav>
<form class="form" method="post" action="{{ route('admin.settings.update') }}">
    @csrf @method('PUT')
    <input type="hidden" name="language_id" value="{{ $current->id }}">
    <fieldset>
        <legend>{{ $current->native_name }}</legend>
        @foreach ($keys as $key => $label)
            <div class="field"><label for="s-{{ $key }}">{{ $label }}</label>
                <input id="s-{{ $key }}" type="text" name="settings[{{ $current->id }}][{{ $key }}]" value="{{ old("settings.{$current->id}.{$key}", $values[$key] ?? '') }}" maxlength="2000"></div>
        @endforeach
    </fieldset>
    <button class="btn" type="submit">ذخیره</button>
</form>
@endsection
