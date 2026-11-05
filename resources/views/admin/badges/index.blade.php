@extends('admin.layout')
@section('title', 'کارت سینه')
@section('content')
<h1>کارت سینه</h1>
<form class="filter" method="get">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="جستجوی نام">
    <select name="status"><option value="">همهٔ وضعیت‌ها</option>@foreach ($statuses as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select>
    <button class="btn ghost" type="submit">فیلتر</button>
</form>
<form method="post" action="{{ route('admin.badges.print') }}" target="_blank">
    @csrf
    <div class="table-wrap"><table>
        <thead><tr><th></th><th>نام</th><th>سمت</th><th>دوره</th><th>وضعیت</th><th>چاپ</th><th></th></tr></thead>
        <tbody>
        @forelse ($badges as $b)
            <tr>
                <td><input type="checkbox" name="ids[]" value="{{ $b->id }}" aria-label="انتخاب {{ $b->full_name }}" @disabled($b->status->value !== 'approved')></td>
                <td>{{ $b->full_name }}</td><td>{{ $b->type->name }}</td><td>{{ $b->edition->code }}</td>
                <td>{{ $statuses[$b->status->value] }}</td><td>{{ $b->print_count }}</td>
                <td class="actions"><a href="{{ route('admin.badges.edit', $b->id) }}">بررسی و ویرایش</a></td>
            </tr>
        @empty
            <tr><td colspan="7">موردی نیست.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <button class="btn" type="submit">چاپ کارت‌های انتخاب‌شده (فقط تأییدشده‌ها)</button>
</form>
{{ $badges->links('pagination::default') }}
@endsection
