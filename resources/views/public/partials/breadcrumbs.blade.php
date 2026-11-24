@if (count($breadcrumbs) > 1)
<nav class="breadcrumbs container" aria-label="{{ __('site.breadcrumb') }}">
    <ol>
        @foreach ($breadcrumbs as $crumb)
            <li>@if ($loop->last)<span aria-current="page">{{ $crumb['name'] }}</span>@else<a href="{{ \App\Support\PublicUrl::path($crumb['path']) }}">{{ $crumb['name'] }}</a>@endif</li>
        @endforeach
    </ol>
</nav>
@endif
