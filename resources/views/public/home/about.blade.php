{{-- "About" panel. Title, text and video come from the panel settings (home_video_*); $features are the links of a module marked "in-about". Shown only with a video file or text. --}}
@php($site = \App\Support\SiteSettings::for(app('site.language')->id))
@if (isset($site['home_video_title']) || isset($site['home_video_text']) || isset($site['home_video_url']))
<section class="about-section" id="about" @isset($site['home_video_title']) aria-labelledby="about-heading" @endisset data-atmosphere>
    <div class="container about-panel">
        <div class="about-copy">
            @isset($site['home_video_title'])<h2 id="about-heading">{{ $site['home_video_title'] }}</h2>@endisset
            @isset($site['home_video_text'])<p class="about-text">{{ $site['home_video_text'] }}</p>@endisset
            @foreach (($features ?? collect()) as $module)
                <ul class="about-links">
                    @foreach ($module->items as $link)
                        @if ($href = $link->href())
                            <li><a href="{{ $href }}">@if ($link->iconMedia)@include('public.partials.image', ['media' => $link->iconMedia, 'class' => 'link-icon', 'alt' => ''])@endif<span><strong>{{ $link->label }}</strong>@if ($link->body)<small>{{ strip_tags($link->body) }}</small>@endif</span></a></li>
                        @endif
                    @endforeach
                </ul>
            @endforeach
        </div>
        @isset($site['home_video_url'])
            <div class="video-player">
                <video controls playsinline preload="none" @isset($site['home_video_poster_url']) poster="{{ $site['home_video_poster_url'] }}" @endisset @isset($site['home_video_title']) aria-label="{{ $site['home_video_title'] }}" @endisset><source src="{{ $site['home_video_url'] }}"></video>
            </div>
        @endisset
    </div>
</section>
@endif
