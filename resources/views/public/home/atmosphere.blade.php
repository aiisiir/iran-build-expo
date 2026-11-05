@php
    $site = \App\Support\SiteSettings::for(app('site.language')->id);
    $video = $site['home_video_url'] ?? asset('video/atmosphere.mp4');
    $poster = $site['home_video_poster_url'] ?? asset('video/atmosphere-poster.jpg');
@endphp
<section class="atmosphere" aria-labelledby="atmosphere-title" data-atmosphere>
    <div class="atmosphere-media" aria-hidden="true">
        <video muted loop playsinline preload="none" poster="{{ $poster }}" data-atmosphere-video><source src="{{ $video }}"></video>
    </div>
    <div class="container atmosphere-inner reveal">
        <h2 id="atmosphere-title">{{ $site['home_video_title'] ?? __('home.atmosphere_title') }}</h2>
        <p>{{ $site['home_video_text'] ?? __('home.atmosphere_text') }}</p>
        <button type="button" class="btn btn-ghost" data-atmosphere-toggle hidden data-label-pause="{{ __('home.video_pause') }}" data-label-play="{{ __('home.video_play') }}">{{ __('home.video_pause') }}</button>
    </div>
</section>
