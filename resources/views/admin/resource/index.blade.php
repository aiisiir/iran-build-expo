@extends('admin.layout')
@section('title', $resource->title())
@section('content')
<div class="head">
    <h1>{{ $resource->title() }} <small class="muted">({{ \App\Support\Digits::toPersian((string) $rows->total()) }})</small></h1>
    @unless ($resource->readOnly)
        <a class="btn" href="{{ route('admin.resource.create', ['resource' => $resource->key(), ...$filters]) }}">+ جدید</a>
    @endunless
</div>
<div class="table-wrap">
<table>
    <thead><tr>@if ($thumbs)<th></th>@endif @foreach ($resource->columns() as $header => $path)<th>{{ $header }}</th>@endforeach @unless ($resource->readOnly)<th></th>@endunless</tr></thead>
    <tbody>
    @forelse ($rows as $row)
        <tr>
            @if ($thumbs)<td class="thumb-cell">@include('admin.partials.thumb', ['asset' => $thumbs[$row->id] ?? null])</td>@endif
            @foreach ($resource->columns() as $path)<td>{{ \App\Admin\Format::cell(data_get($row, $path)) }}</td>@endforeach
            @unless ($resource->readOnly)
                <td class="actions">
                    <a href="{{ route('admin.resource.edit', ['resource' => $resource->key(), 'id' => $row->id]) }}">ویرایش</a>
                    @if ($url = $resource->viewUrl($row))<a href="{{ $url }}" target="_blank" rel="noopener">مشاهده</a>@endif
                    @if ($resource->copyable)
                        <form method="post" action="{{ route('admin.resource.replicate', ['resource' => $resource->key(), 'id' => $row->id]) }}">
                            @csrf <button class="link" type="submit">کپی</button>
                        </form>
                    @endif
                    @if ($resource->canDelete($row))
                        <form method="post" action="{{ route('admin.resource.destroy', ['resource' => $resource->key(), 'id' => $row->id]) }}" data-confirm="این مورد حذف شود؟ (از «رکوردهای حذف‌شده» قابل بازیابی است)">
                            @csrf @method('DELETE') <button class="link danger" type="submit">حذف</button>
                        </form>
                    @endif
                </td>
            @endunless
        </tr>
    @empty
        <tr><td colspan="{{ count($resource->columns()) + 2 }}">موردی وجود ندارد.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $rows->links('pagination::default') }}
@endsection
