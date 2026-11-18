@extends('admin.layout')
@section('title', 'رکوردهای حذف‌شده')
@section('content')
<div class="head"><h1>رکوردهای حذف‌شده</h1></div>
<p class="muted">رکورد حذف‌شده از سایت برداشته می‌شود اما در پایگاه داده می‌ماند. با «بازیابی» به همان جای قبلی برمی‌گردد.</p>
<div class="table-wrap">
<table>
    <thead><tr><th>نوع رکورد</th><th>عنوان</th><th>زمان حذف</th><th></th></tr></thead>
    <tbody>
    @forelse ($rows as $row)
        <tr>
            <td><span class="tag">{{ $row['resource']->title() }}</span></td>
            <td>{{ \App\Admin\Format::cell($row['title'] ?? '#'.$row['model']->id) }}</td>
            <td>{{ \App\Admin\Format::cell($row['model']->deleted_at) }}</td>
            <td class="actions">
                <form method="post" action="{{ route('admin.resource.restore', ['resource' => $row['resource']->key(), 'id' => $row['model']->id]) }}">
                    @csrf <input type="hidden" name="from" value="trash"><button class="link" type="submit">بازیابی</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="4">رکورد حذف‌شده‌ای وجود ندارد.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@if ($more)<p class="muted">فقط جدیدترین {{ \App\Support\Digits::toPersian((string) $rows->count()) }} مورد نمایش داده شد.</p>@endif
@endsection
