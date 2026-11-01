{{-- $media: MediaAsset; optional: $class, $eager, $alt (overrides the stored alt) --}}
<img @if (! empty($class)) class="{{ $class }}" @endif src="{{ $media->url() }}" alt="{{ $alt ?? $media->alt }}"
     @if ($media->width && $media->height) width="{{ $media->width }}" height="{{ $media->height }}" @endif
     @if ($media->title) title="{{ $media->title }}" @endif
     loading="{{ ! empty($eager) ? 'eager' : 'lazy' }}" decoding="async">
