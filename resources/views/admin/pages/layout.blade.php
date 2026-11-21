@extends('admin.layout')
@section('title', 'چینش بلوک‌ها')
@section('content')
<div class="head">
    <h1>چینش بلوک‌های «{{ $page->title }}»</h1>
    <a href="{{ route('admin.resource.edit', ['resource' => 'pages', 'id' => $page->id]) }}">بازگشت به صفحه</a>
    <a class="btn ghost" href="{{ route('admin.resource.create', ['resource' => 'page-modules', 'page_id' => $page->id]) }}">+ بلوک جدید</a>
</div>
<p class="muted">بلوک‌ها را با دستگیرهٔ ⠿ بکشید یا با پیکان‌ها جابه‌جا کنید؛ ترتیب با «ذخیره» اعمال می‌شود. بلوک خاموش در سایت دیده نمی‌شود.</p>
<form method="post" action="{{ route('admin.pages.layout.update', $page->id) }}">
    @csrf @method('PUT')
    <ol class="layout-list" data-sortable>
        @forelse ($modules as $m)
            <li class="layout-row" data-row>
                <span class="grip" draggable="true" title="بکشید" aria-hidden="true">⠿</span>
                <span class="move" hidden data-move-box>
                    <button type="button" class="link" data-move="-1" aria-label="بالا">▲</button>
                    <button type="button" class="link" data-move="1" aria-label="پایین">▼</button>
                </span>
                <input type="hidden" name="order[]" value="{{ $m->id }}">
                <span class="info">
                    <span class="tag">{{ \App\Admin\Options::moduleTypes()[$m->type->value] ?? $m->type->value }}</span>
                    <strong>{{ $m->title ?: '— بدون عنوان —' }}</strong>
                    @if ($m->category) <small class="muted">دستهٔ «{{ $m->category->name }}»</small> @endif
                </span>
                <label class="check"><input type="checkbox" name="active[{{ $m->id }}]" value="1" @checked($m->is_active)> فعال</label>
                <label class="check"><input type="checkbox" name="titles[{{ $m->id }}]" value="1" @checked($m->show_title)> نمایش عنوان</label>
                @if (in_array($m->type, $limited, true))
                    <label class="check">تعداد <input type="number" name="limit[{{ $m->id }}]" value="{{ $m->item_limit }}" min="1" max="50" dir="ltr"></label>
                @else
                    <span class="muted">—</span>
                @endif
                <span class="row-links">
                    @if ($recordUrls[$m->id]) <a href="{{ $recordUrls[$m->id] }}">رکوردها</a> @endif
                    <a href="{{ route('admin.resource.edit', ['resource' => 'page-modules', 'id' => $m->id]) }}">تنظیمات</a>
                </span>
            </li>
        @empty
            <li class="muted">این صفحه هنوز بلوکی ندارد.</li>
        @endforelse
    </ol>
    @if ($modules->isNotEmpty()) <button class="btn" type="submit">ذخیره</button> @endif
</form>
@endsection
