<section class="section" aria-labelledby="pillars-title">
    <div class="container">
        <h2 id="pillars-title" class="section-title">{{ __('home.pillars_title') }}</h2>
        <ul class="pillars">
            @foreach (__('home.pillars') as $i => $p)
                <li class="pillar reveal"><span class="pillar-no" aria-hidden="true">{{ \App\Support\Digits::forLocale((string) ($i + 1), app('site.language')->code) }}</span><h3>{{ $p['title'] }}</h3><p>{{ $p['text'] }}</p></li>
            @endforeach
        </ul>
    </div>
</section>
