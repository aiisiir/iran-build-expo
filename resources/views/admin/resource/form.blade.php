@extends('admin.layout')
@section('title', $resource->title())
@section('content')
<div class="head">
    <h1>{{ $model->exists ? 'ویرایش' : 'افزودن' }} — {{ $resource->title() }}</h1>
    <a href="{{ route('admin.resource.index', ['resource' => $resource->key()]) }}">بازگشت به فهرست</a>
</div>
@if ($model->exists)
    <p class="children">@foreach ($resource->children($model) as $label => $url)<a class="btn ghost" href="{{ $url }}">{{ $label }}</a>@endforeach</p>
@endif
<form class="form" method="post" enctype="multipart/form-data"
      action="{{ $model->exists ? route('admin.resource.update', ['resource' => $resource->key(), 'id' => $model->id]) : route('admin.resource.store', ['resource' => $resource->key()]) }}">
    @csrf
    @if ($model->exists) @method('PUT') @endif
    @foreach ($resource->fields($model->exists ? $model : null) as $field)
        @include('admin.partials.field', ['field' => $field, 'model' => $model])
    @endforeach
    <button class="btn" type="submit">ذخیره</button>
</form>
@endsection
