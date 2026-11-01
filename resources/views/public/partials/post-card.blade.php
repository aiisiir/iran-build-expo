@php($href = $post->link_url ?: \App\Support\PublicUrl::path($post->path()))
<article class="card">
    @if ($post->image)<a class="card-media" href="{{ $href }}" tabindex="-1" aria-hidden="true">@include('public.partials.image', ['media' => $post->image, 'alt' => ''])</a>@endif
    <div class="card-body">
        @if ($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ \App\Support\DateFormat::show($post->published_at, $language) }}</time>@endif
        <h3><a href="{{ $href }}">{{ $post->title }}</a></h3>
        @if ($post->lead || $post->summary)<p>{{ $post->summary ?: $post->lead }}</p>@endif
    </div>
</article>
