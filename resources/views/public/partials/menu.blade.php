@if ($depth < 6 && $byParent->has($parent))
<ul class="menu depth-{{ $depth }}">
    @foreach ($byParent->get($parent) as $link)
        <li @class(['has-sub' => $byParent->has($link->id)])>
            @include('public.partials.link', ['link' => $link])
            @include('public.partials.menu', ['byParent' => $byParent, 'parent' => $link->id, 'depth' => $depth + 1])
        </li>
    @endforeach
</ul>
@endif
