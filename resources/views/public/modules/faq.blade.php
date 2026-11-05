<div class="faq">
    @foreach ($module->items as $faq)
        <details><summary>{{ $faq->question }}</summary><div class="prose">{!! \App\Support\HtmlSanitizer::clean($faq->answer) !!}</div></details>
    @endforeach
</div>
