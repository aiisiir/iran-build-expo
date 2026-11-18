@extends('admin.layout')
@section('title', $resource->title())
@section('content')
<div class="head">
    <h1>{{ $resource->title() }}</h1>
    @if ($resource->hasTrash())
        <a class="btn ghost" href="{{ route('admin.resource.index', ['resource' => $resource->key(), ...$filters, ...($trashed ? [] : ['trashed' => 1])]) }}">{{ $trashed ? 'بازگشت به فهرست' : 'سطل بازیافت' }}</a>
    @endif
    @unless ($resource->readOnly || $trashed)
        <a class="btn" href="{{ route('admin.resource.create', ['resource' => $resource->key(), ...$filters]) }}">+ جدید</a>
    @endunless
</div>
<div class="table-wrap">
<table>
    <thead><tr>@foreach ($resource->columns() as $header => $path)<th>{{ $header }}</th>@endforeach @unless ($resource->readOnly)<th></th>@endunless</tr></thead>
    <tbody>
    @forelse ($rows as $row)
        <tr>
            @foreach ($resource->columns() as $path)<td>{{ \App\Admin\Format::cell(data_get($row, $path)) }}</td>@endforeach
            @if ($trashed)
                <td class="actions">
                    <form method="post" action="{{ route('admin.resource.restore', ['resource' => $resource->key(), 'id' => $row->id]) }}">
                        @csrf <button class="link" type="submit">بازیابی</button>
                    </form>
                </td>
            @elseif (! $resource->readOnly)
                <td class="actions">
                    <a href="{{ route('admin.resource.edit', ['resource' => $resource->key(), 'id' => $row->id]) }}">ویرایش</a>
                    @if ($resource->canDelete($row))
                        <form method="post" action="{{ route('admin.resource.destroy', ['resource' => $resource->key(), 'id' => $row->id]) }}" data-confirm="این مورد حذف شود؟">
                            @csrf @method('DELETE') <button class="link danger" type="submit">حذف</button>
                        </form>
                    @endif
                </td>
            @endif
        </tr>
    @empty
        <tr><td colspan="{{ count($resource->columns()) + 1 }}">{{ $trashed ? 'سطل بازیافت خالی است.' : 'موردی وجود ندارد.' }}</td></tr>
    @endforelse
    </tbody>
</table>
</div>
{{ $rows->links('pagination::default') }}
@endsection
