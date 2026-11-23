{{-- About panel: title, text (body), feature links (link group) and a video whose poster is the block's picture. --}}
<div class="about-copy">
    @if ($module->show_title && $module->title)<h2 id="m-{{ $module->id }}">{{ $module->title }}</h2>@endif
    @if ($module->body)<div class="about-text">{!! \App\Support\HtmlSanitizer::clean($module->body) !!}</div>@endif
    @if ($module->items->isNotEmpty())
        <ul class="about-links">
            @foreach ($module->items as $link)
                @if ($href = $link->href())
                    <li><a href="{{ $href }}">@if ($link->iconMedia)@include('public.partials.image', ['media' => $link->iconMedia, 'class' => 'link-icon', 'alt' => ''])@endif<span><strong>{{ $link->label }}</strong>@if ($link->body)<small>{{ strip_tags($link->body) }}</small>@endif</span></a></li>
                @endif
            @endforeach
        </ul>
    @endif
</div>
@if ($module->video_url)
    <div class="video-player">
        <video controls playsinline preload="none" @if ($module->media) poster="{{ $module->media->url() }}" @endif @if ($module->title) aria-label="{{ $module->title }}" @endif><source src="{{ $module->video_url }}"></video>
    </div>
@endif
