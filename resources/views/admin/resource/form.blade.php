@extends('admin.layout')
@section('title', $resource->title())
@section('content')
<div class="head">
    <h1>{{ $model->exists ? 'ویرایش' : 'افزودن' }} — {{ $resource->title() }}</h1>
    <a href="{{ route('admin.resource.index', ['resource' => $resource->key()]) }}">بازگشت به فهرست</a>
</div>
@if ($model->exists)
    <p class="children">@foreach ($resource->children($model) as $label => $url)<a class="btn ghost" href="{{ $url }}" @if (str_starts_with($url, '/') && ! str_starts_with($url, '/admin')) target="_blank" rel="noopener" @endif>{{ $label }}</a>@endforeach</p>
@endif
@if ($model->exists && $resource->outlineView())
    @include($resource->outlineView(), ['model' => $model])
@endif
<form class="form" method="post" enctype="multipart/form-data"
      action="{{ $model->exists ? route('admin.resource.update', ['resource' => $resource->key(), 'id' => $model->id]) : route('admin.resource.store', ['resource' => $resource->key()]) }}">
    @csrf
    @if ($model->exists) @method('PUT') @endif
    @php
        $tabs = collect($resource->fields($model->exists ? $model : null))->groupBy(fn ($f) => $f->tab())
            ->sortBy(fn ($fields, $tab) => array_search($tab, array_keys(\App\Admin\Field::TABS)));
        $withErrors = $tabs->map(fn ($fields) => $fields->contains(fn ($f) => $errors->has($f->name)));
        $active = $withErrors->filter()->keys()->first() ?? $tabs->keys()->first();
    @endphp
    @if ($tabs->count() > 1)
        {{-- Hidden until admin.js turns it on: without JavaScript every section is simply shown one after another. --}}
        <div class="form-tabs" role="tablist" hidden>
            @foreach ($tabs as $tab => $fields)
                <button type="button" role="tab" id="tab-{{ $tab }}" aria-controls="panel-{{ $tab }}" aria-selected="{{ $tab === $active ? 'true' : 'false' }}" @class(['has-error' => $withErrors[$tab]])>{{ \App\Admin\Field::TABS[$tab] }}@if ($withErrors[$tab]) ●@endif</button>
            @endforeach
        </div>
    @endif
    @foreach ($tabs as $tab => $fields)
        <div class="tab-panel" role="tabpanel" id="panel-{{ $tab }}" aria-labelledby="tab-{{ $tab }}">
            @foreach ($fields as $field)
                @include('admin.partials.field', ['field' => $field, 'model' => $model])
            @endforeach
        </div>
    @endforeach
    <button class="btn" type="submit">ذخیره</button>
</form>
@endsection
