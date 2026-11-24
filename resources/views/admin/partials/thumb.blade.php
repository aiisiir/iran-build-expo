@if ($asset && $asset->kind === \App\Enums\MediaKind::Image)
    <img class="thumb" src="{{ $asset->url() }}" alt="{{ $asset->alt }}" loading="lazy" width="48" height="48">
@elseif ($asset)
    <span class="thumb thumb-file" title="{{ $asset->original_name }}">PDF</span>
@endif
