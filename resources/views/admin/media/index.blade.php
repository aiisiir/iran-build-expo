@extends('admin.layout')
@section('title', 'رسانه‌ها')
@section('content')
<h1>رسانه‌ها</h1>
<form class="form inline" method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="field"><label for="f-file">فایل (jpg، png، webp، svg تا ۲ مگابایت؛ pdf تا ۲۰ مگابایت)</label>
        <input id="f-file" type="file" name="file" required accept=".jpg,.jpeg,.png,.webp,.svg,.pdf">@error('file')<small class="error" role="alert">{{ $message }}</small>@enderror</div>
    <div class="field"><label for="f-lang">زبان (برای متن جایگزین)</label>
        <select id="f-lang" name="language_id"><option value="">—</option>@foreach (\App\Admin\Options::languages() as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach</select></div>
    <div class="field"><label for="f-alt">متن جایگزین (alt)</label><input id="f-alt" type="text" name="alt" maxlength="191"></div>
    <div class="field"><label for="f-title">عنوان</label><input id="f-title" type="text" name="title" maxlength="191"></div>
    <button class="btn" type="submit">بارگذاری</button>
</form>
<div class="table-wrap"><table>
    <thead><tr><th></th><th>نام</th><th>نشانی برای متن‌ها</th><th>زبان</th><th>alt</th><th></th></tr></thead>
    <tbody>
    @forelse ($assets as $a)
        <tr>
            <td>@if ($a->kind->value === 'image')<img src="{{ $a->url() }}" alt="" width="56" height="56" loading="lazy" class="thumb">@else PDF @endif</td>
            <td>{{ $a->original_name }}</td>
            <td dir="ltr"><code>/uploads/{{ $a->path }}</code></td>
            <td>{{ $a->language?->code ?? '—' }}</td>
            <td>{{ $a->alt ?: '—' }}</td>
            <td class="actions"><a href="{{ route('admin.media.edit', $a->id) }}">ویرایش</a>
                <form method="post" action="{{ route('admin.media.destroy', $a->id) }}" data-confirm="این فایل حذف شود؟">@csrf @method('DELETE')<button class="link danger" type="submit">حذف</button></form></td>
        </tr>
    @empty
        <tr><td colspan="6">فایلی نیست.</td></tr>
    @endforelse
    </tbody>
</table></div>
{{ $assets->links('pagination::default') }}
@endsection
