{{-- Each tile leads to the gallery page (all images); there a click opens the modal. --}}
@php($url = fn ($item) => route('public.gallery', ['locale' => app('site.language')->code, 'id' => $item->gallery_id]))
@if (in_array('gallery-section', $classes, true))
    {{-- shortcut: the mosaic is laid out for 3 + 4 + 3 pictures; fewer pictures leave empty cells, more are ignored. Upgrade if the layout must adapt. --}}
    <div class="gallery-mosaic">
        @foreach ([['one', 0, 3], ['two', 3, 4], ['three', 7, 3]] as [$row, $from, $count])
            @if ($module->items->slice($from, $count)->isNotEmpty())
                <div class="gallery-row gallery-row-{{ $row }}">
                    @foreach ($module->items->slice($from, $count) as $item)
                        <a class="gallery-photo" href="{{ $url($item) }}">
                            @include('public.partials.image', ['media' => $item->media, 'alt' => $item->caption ?: ($item->media->alt ?? '')])
                            <span class="gallery-zoom" aria-hidden="true"><x-icon name="zoom" /></span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
@else
    <ul class="gallery">
        @foreach ($module->items as $item)
            <li><figure><a href="{{ $url($item) }}">@include('public.partials.image', ['media' => $item->media])</a>@if ($item->caption)<figcaption>{{ $item->caption }}</figcaption>@endif</figure></li>
        @endforeach
    </ul>
@endif
