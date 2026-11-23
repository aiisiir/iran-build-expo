@extends('layouts.app')

@section('content')
{{-- No home page exists yet: the panel creates it (Pages > home), so nothing is written here. --}}
<h1 class="sr-only">{{ \App\Support\SiteSettings::for(app('site.language')->id)['site_name'] ?? config('app.name') }}</h1>
@endsection
