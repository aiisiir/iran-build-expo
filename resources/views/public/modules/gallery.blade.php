{{-- Every picture opens in the viewer on this page; without JavaScript the link opens the full image. --}}
@include('public.partials.lightbox')
@if (in_array('gallery-section', $classes, true))
    {{-- shortcut: the mosaic is laid out for 3 + 4 + 3 pictures; fewer pictures leave empty cells, more are ignored. Upgrade if the layout must adapt. --}}
    <div class="gallery-mosaic" data-lightbox>
        @foreach ([['one', 0, 3], ['two', 3, 4], ['three', 7, 3]] as [$row, $from, $count])
            @if ($module->items->slice($from, $count)->isNotEmpty())
                <div class="gallery-row gallery-row-{{ $row }}">
                    @foreach ($module->items->slice($from, $count) as $item)
                        <a class="gallery-photo" href="{{ $item->media->url() }}" data-caption="{{ $item->caption }}">
                            @include('public.partials.image', ['media' => $item->media, 'alt' => $item->caption ?: ($item->media->alt ?? '')])
                            <span class="gallery-zoom" aria-hidden="true"><x-icon name="zoom" /></span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
@else
    <ul class="gallery" data-lightbox>
        @foreach ($module->items as $item)
            <li><figure><a href="{{ $item->media->url() }}" data-caption="{{ $item->caption }}">@include('public.partials.image', ['media' => $item->media])</a>@if ($item->caption)<figcaption>{{ $item->caption }}</figcaption>@endif</figure></li>
        @endforeach
    </ul>
@endif
