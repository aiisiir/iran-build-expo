{{-- Line icons of the Benis design. <x-icon name="chevron" class="…"/> ; unknown names fall back to "file". --}}
@props(['name'])
@php
    $paths = [
        'users' => '<circle cx="12" cy="7" r="3"/><path d="M6 21v-3a6 6 0 0 1 12 0v3M4 10a3 3 0 1 1 2-5M20 10a3 3 0 1 0-2-5M1 20v-3a5 5 0 0 1 3-4M23 20v-3a5 5 0 0 0-3-4"/>',
        'globe' => '<circle cx="12" cy="10" r="8"/><ellipse cx="12" cy="10" rx="3.5" ry="8"/><path d="M5 5h14M4 10h16M5 15h14M3 22v-2l4-2M21 22v-2l-4-2M8 22v-3M16 22v-3M8 22h8"/>',
        'monitor' => '<rect x="2" y="3" width="20" height="15" rx="2"/><path d="M9 18v3m6-3v3M7 22h10M5 6h6v6H5zM14 6h5m-5 3h5M5 15h14M14 12h5"/>',
        'clipboard' => '<rect x="5" y="4" width="16" height="18" rx="2"/><path d="M10 2h6v4h-6zM12 10h6m-6 4h6m-6 4h6M3 13V9"/><circle cx="5" cy="11" r="3"/><path d="m3 14-1 6 3-1 3 1-1-6"/>',
        'map' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'image' => '<rect x="2" y="3" width="20" height="18" rx="2"/><circle cx="16" cy="8" r="2"/><path d="m2 17 6-7 6 7 3-3 5 5"/>',
        'download' => '<path d="M12 2v13m-5-5 5 5 5-5M3 16v5h18v-5"/>',
        'award' => '<circle cx="12" cy="9" r="6"/><path d="m8 14-2 8 6-3 6 3-2-8M9 9l2 2 4-4"/>',
        'headset' => '<path d="M3 14v-3a9 9 0 0 1 18 0v3M3 12h3v8H3zM18 12h3v8h-3zM18 20c0 2-3 2-6 2"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="17" rx="3"/><path d="M7 2v6M17 2v6M3 11h18M7 15h2m3 0h2m3 0h1M7 18h2m3 0h2"/>',
        'chevron' => '<path d="m9 5 7 7-7 7"/>',
        'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="3"/><path d="m3 6 9 7 9-7"/>',
        'phone' => '<path d="m5 3 4 4-2 3a15 15 0 0 0 7 7l3-2 4 4-2 3C10 23 1 14 2 5Z"/>',
        'location' => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'building' => '<path d="M3 22V7l9-5 9 5v15H3ZM1 22h22M7 9h2m6 0h2M7 13h2m6 0h2M10 22v-5h4v5"/>',
        'layers' => '<path d="m12 2 10 6-10 6L2 8Zm-10 10 10 6 10-6M2 17l10 6 10-6"/>',
        'external' => '<path d="M14 3h7v7m0-7L10 14M10 3H3v18h18v-7"/>',
        'file' => '<path d="M14 2H4v20h16V8Zm0 0v6h6M8 12h8M8 16h8M8 19h5"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'zoom' => '<circle cx="10" cy="10" r="7"/><path d="m15 15 6 6M7 10h6m-3-3v6"/>',
        'broadcast' => '<path d="M5 18a10 10 0 1 1 14 0M8 15a6 6 0 1 1 8 0M12 12v9m-4 0h8"/><circle cx="12" cy="9" r="2"/>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? $paths['file'] !!}</svg>
