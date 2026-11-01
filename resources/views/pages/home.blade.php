@extends('layouts.app')

@section('content')
<section class="hero container" aria-labelledby="home-title">
    <p class="eyebrow">{{ __('home.eyebrow') }}</p>
    <h1 id="home-title">{{ __('home.title') }}</h1>
    <p class="hero-copy">{{ __('home.intro') }}</p>
</section>
@endsection
