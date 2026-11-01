@extends('admin.layout')
@section('title', 'ویرایش رسانه')
@section('content')
<h1>ویرایش رسانه</h1>
@if ($asset->kind->value === 'image')<p><img src="{{ $asset->url() }}" alt="{{ $asset->alt }}" class="preview"></p>@endif
<form class="form" method="post" action="{{ route('admin.media.update', $asset->id) }}">
    @csrf @method('PUT')
    <div class="field"><label for="f-lang">زبان</label>
        <select id="f-lang" name="language_id"><option value="">—</option>@foreach (\App\Admin\Options::languages() as $id => $label)<option value="{{ $id }}" @selected($asset->language_id == $id)>{{ $label }}</option>@endforeach</select></div>
    <div class="field"><label for="f-alt">متن جایگزین (alt)</label><input id="f-alt" type="text" name="alt" value="{{ old('alt', $asset->alt) }}" maxlength="191"></div>
    <div class="field"><label for="f-title">عنوان</label><input id="f-title" type="text" name="title" value="{{ old('title', $asset->title) }}" maxlength="191"></div>
    <button class="btn" type="submit">ذخیره</button>
</form>
@endsection
