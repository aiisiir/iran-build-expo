{{-- Each tile leads to the gallery page (all images); there a click opens the modal. --}}
<ul class="gallery">
    @foreach ($module->items as $item)
        <li><figure><a href="{{ route('public.gallery', ['locale' => app('site.language')->code, 'id' => $item->gallery_id]) }}">@include('public.partials.image', ['media' => $item->media])</a>@if ($item->caption)<figcaption>{{ $item->caption }}</figcaption>@endif</figure></li>
    @endforeach
</ul>
