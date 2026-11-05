@extends('layouts.app')

@section('content')
<h1 class="sr-only">{{ \App\Support\SiteSettings::for(app('site.language')->id)['site_name'] ?? config('app.name') }}</h1>
@include('public.home.hero')
@include('public.home.pillars')
@include('public.home.atmosphere')
@endsection
