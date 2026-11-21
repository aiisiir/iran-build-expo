@php
    $model->loadMissing('language', 'modules.sourcePage', 'modules.category');
    $path = \App\Support\PublicUrl::path($model->path());
@endphp
<section class="outline" aria-label="ساختار صفحه">
    <p><strong>آدرس:</strong> <a dir="ltr" href="{{ $path }}" target="_blank" rel="noopener">{{ url($path) }}</a>
        @unless ($model->isPublic()) <em class="muted">(هنوز منتشر نشده؛ برای بازدیدکننده 404 است)</em> @endunless</p>
    <ol class="outline-list">
        @forelse ($model->modules as $m)
            <li @class(['off' => ! $m->is_active])>
                <span class="tag">{{ $m->type->value }}</span>
                <strong>{{ $m->title ?: '— بدون عنوان —' }}</strong>
                <small>{{ $m->display->value }} · حداکثر {{ $m->item_limit }}
                    @if ($m->sourcePage) · از «{{ $m->sourcePage->title }}» @endif
                    @if ($m->category) · دسته «{{ $m->category->name }}» @endif
                    @unless ($m->is_active) · غیرفعال @endunless</small>
                <a href="{{ route('admin.resource.edit', ['resource' => 'page-modules', 'id' => $m->id]) }}">ویرایش</a>
            </li>
        @empty
            <li class="muted">این صفحه هنوز بلوکی ندارد.</li>
        @endforelse
    </ol>
</section>
