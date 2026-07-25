@extends('layouts.admin')
@section('title', 'News')
@section('content')

<form method="POST" action="{{ route('admin.news.store') }}" class="glass-card mb-6 grid gap-3 p-5 md:grid-cols-2">@csrf
<input name="title" class="input-field" placeholder="Title" required>
<select name="sentiment" class="input-field"><option>neutral</option><option>bullish</option><option>bearish</option></select>
<input name="source" class="input-field" placeholder="Source">
<textarea name="summary" class="input-field md:col-span-2" placeholder="Summary"></textarea>
<textarea name="content" class="input-field md:col-span-2" placeholder="Content"></textarea>
<button class="btn-brand md:col-span-2">Publish</button>
</form>
@foreach($articles as $a)<div class="glass-card mb-2 p-3 text-sm">{{ $a->title }} · {{ $a->sentiment }}</div>@endforeach{{ $articles->links() }}

@endsection
