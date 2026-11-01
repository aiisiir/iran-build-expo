@extends('layouts.app')
@section('content')
<section class="container article narrow">
    <h1>{{ __('badge.title') }}</h1>
    @if (session('sent'))
        <p class="notice ok" role="status">{{ __('badge.sent') }}</p>
    @elseif (! $edition)
        <p class="notice">{{ __('badge.closed') }}</p>
    @else
        <p>{{ __('badge.intro') }}</p>
        <form class="badge-form" method="post" action="{{ route('public.badge.store', ['locale' => $language->code]) }}" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            @foreach ([['full_name', 'text'], ['phone', 'tel'], ['email', 'email'], ['age', 'text'], ['address', 'text'], ['education_degree', 'text'], ['education_field', 'text'], ['activity_field', 'text']] as [$name, $type])
                <div class="field @error($name) has-error @enderror">
                    <label for="b-{{ $name }}">{{ __('badge.'.$name) }}</label>
                    <input id="b-{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" @if (in_array($name, ['phone', 'email'])) dir="ltr" @endif
                           @if (in_array($name, ['full_name', 'phone', 'email'])) required @endif>
                    @error($name)<small class="error" role="alert">{{ $message }}</small>@enderror
                </div>
            @endforeach
            <div class="field @error('gender') has-error @enderror"><label for="b-gender">{{ __('badge.gender') }}</label>
                <select id="b-gender" name="gender" required><option value="">—</option>@foreach (['male', 'female'] as $g)<option value="{{ $g }}" @selected(old('gender') === $g)>{{ __('badge.'.$g) }}</option>@endforeach</select>
                @error('gender')<small class="error" role="alert">{{ $message }}</small>@enderror</div>
            <div class="field @error('badge_type_id') has-error @enderror"><label for="b-type">{{ __('badge.type') }}</label>
                <select id="b-type" name="badge_type_id" required><option value="">—</option>@foreach ($types as $id => $name)<option value="{{ $id }}" @selected((int) old('badge_type_id') === $id)>{{ $name }}</option>@endforeach</select>
                @error('badge_type_id')<small class="error" role="alert">{{ $message }}</small>@enderror</div>
            <div class="field @error('photo') has-error @enderror"><label for="b-photo">{{ __('badge.photo') }}</label>
                <input id="b-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required>@error('photo')<small class="error" role="alert">{{ $message }}</small>@enderror</div>
            <label class="check"><input type="checkbox" name="consent" value="1" required> {{ __('badge.consent') }}</label>
            @error('consent')<small class="error" role="alert">{{ $message }}</small>@enderror
            <button class="btn" type="submit">{{ __('badge.submit') }}</button>
        </form>
    @endif
</section>
@endsection
