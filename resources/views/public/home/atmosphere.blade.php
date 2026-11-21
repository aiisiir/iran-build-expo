{{-- The video band exists only when "home_video_url" is set in the panel settings. --}}
@php($site = \App\Support\SiteSettings::for(app('site.language')->id))
@isset($site['home_video_url'])
<section class="atmosphere" @isset($site['home_video_title']) aria-labelledby="atmosphere-title" @endisset data-atmosphere>
    <div class="atmosphere-media" aria-hidden="true">
        <video muted loop playsinline preload="none" @isset($site['home_video_poster_url']) poster="{{ $site['home_video_poster_url'] }}" @endisset data-atmosphere-video><source src="{{ $site['home_video_url'] }}"></video>
    </div>
    <div class="container atmosphere-inner reveal">
        @isset($site['home_video_title'])<h2 id="atmosphere-title">{{ $site['home_video_title'] }}</h2>@endisset
        @isset($site['home_video_text'])<p>{{ $site['home_video_text'] }}</p>@endisset
    </div>
</section>
@endisset
