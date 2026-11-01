<ul class="gallery">
    @foreach ($module->items as $item)
        <li><figure>@include('public.partials.image', ['media' => $item->media])@if ($item->caption)<figcaption>{{ $item->caption }}</figcaption>@endif</figure></li>
    @endforeach
</ul>
