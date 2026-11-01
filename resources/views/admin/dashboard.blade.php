@extends('admin.layout')
@section('title', 'داشبورد')
@section('content')
<h1>داشبورد</h1>
<div class="cards">
    @foreach ($counts as $label => $count)
        <div class="card"><strong>{{ \App\Support\Digits::toPersian((string) $count) }}</strong><span>{{ $label }}</span></div>
    @endforeach
</div>
@if ($events->isNotEmpty())
    <h2>آخرین فعالیت‌ها</h2>
    <table>
        @foreach ($events as $e)
            <tr><td>{{ \App\Admin\Format::cell($e->created_at) }}</td><td>{{ $e->event }}</td><td>{{ $e->subject_type }} {{ $e->subject_id }}</td><td dir="ltr">{{ $e->ip }}</td></tr>
        @endforeach
    </table>
@endif
@endsection
