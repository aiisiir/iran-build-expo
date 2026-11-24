<aside class="recent" aria-label="رکوردهای {{ $resource->title() }}">
    <h2>{{ $resource->title() }} <small class="muted">(آخرین‌ها)</small></h2>
    <ul>
        @foreach ($recent as $row)
            <li @class(['current' => $model->exists && $model->id === $row->id])>
                @include('admin.partials.thumb', ['asset' => $recentThumbs[$row->id] ?? null])
                <a class="title" href="{{ route('admin.resource.edit', ['resource' => $resource->key(), 'id' => $row->id]) }}">{{ $resource->rowTitle($row) }}</a>
                <span class="row-actions">
                    @if ($resource->copyable)
                        <form method="post" action="{{ route('admin.resource.replicate', ['resource' => $resource->key(), 'id' => $row->id]) }}">@csrf<button class="link" type="submit">کپی</button></form>
                    @endif
                    @if ($resource->canDelete($row))
                        <form method="post" action="{{ route('admin.resource.destroy', ['resource' => $resource->key(), 'id' => $row->id]) }}" data-confirm="این مورد حذف شود؟ (از «رکوردهای حذف‌شده» قابل بازیابی است)">@csrf @method('DELETE')<button class="link danger" type="submit">حذف</button></form>
                    @endif
                </span>
            </li>
        @endforeach
    </ul>
    <a href="{{ route('admin.resource.index', ['resource' => $resource->key()]) }}">همهٔ رکوردها ←</a>
</aside>
