@extends('layouts.app')

@section('content')
<section class="hero" aria-labelledby="home-title">
    <p class="eyebrow">{{ __('home.eyebrow') }}</p>
    <h1 id="home-title">{{ __('home.title') }}</h1>
    <p class="hero-copy">{{ __('home.intro') }}</p>
    <p class="implementation-note">{{ __('home.pilot_note') }}</p>
</section>
@endsection
