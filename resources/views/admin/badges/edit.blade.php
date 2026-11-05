@extends('admin.layout')
@section('title', 'بررسی کارت سینه')
@section('content')
<div class="head"><h1>بررسی کارت سینه</h1><a href="{{ route('admin.badges.index') }}">بازگشت</a></div>
<form class="form" method="post" action="{{ route('admin.badges.update', $badge->id) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <p><img class="preview" src="{{ route('admin.badges.photo', $badge->id) }}" alt="عکس {{ $badge->full_name }}" width="150"></p>
    @php($v = fn ($k, $d = null) => old($k, $badge->{$k} instanceof BackedEnum ? $badge->{$k}->value : ($badge->{$k} ?? $d)))
    @foreach ([['full_name', 'نام و نام خانوادگی'], ['phone', 'شماره تماس'], ['email', 'ایمیل'], ['age', 'سن'], ['address', 'آدرس'], ['education_degree', 'مدرک تحصیلی'], ['education_field', 'گرایش تحصیلی'], ['activity_field', 'حوزهٔ فعالیت']] as [$k, $label])
        <div class="field @error($k) has-error @enderror"><label for="f-{{ $k }}">{{ $label }}</label><input id="f-{{ $k }}" type="text" name="{{ $k }}" value="{{ $v($k) }}">@error($k)<small class="error" role="alert">{{ $message }}</small>@enderror</div>
    @endforeach
    <div class="field"><label for="f-gender">جنسیت</label><select id="f-gender" name="gender">@foreach (\App\Admin\Options::genders() as $val => $l)<option value="{{ $val }}" @selected($v('gender') === $val)>{{ $l }}</option>@endforeach</select></div>
    <div class="field"><label for="f-type">سمت روی کارت</label><select id="f-type" name="badge_type_id">@foreach ($types as $id => $name)<option value="{{ $id }}" @selected((int) $v('badge_type_id') === $id)>{{ $name }}</option>@endforeach</select></div>
    <div class="field"><label for="f-status">وضعیت</label><select id="f-status" name="status">@foreach ($statuses as $val => $l)<option value="{{ $val }}" @selected($v('status') === $val)>{{ $l }}</option>@endforeach</select></div>
    <div class="field"><label for="f-photo">جایگزینی عکس</label><input id="f-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp">@error('photo')<small class="error" role="alert">{{ $message }}</small>@enderror</div>
    <button class="btn" type="submit">ذخیره</button>
</form>
<form method="post" action="{{ route('admin.badges.destroy', $badge->id) }}" data-confirm="این ثبت حذف شود؟">@csrf @method('DELETE')<button class="link danger" type="submit">حذف</button></form>
@endsection
