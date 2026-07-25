@extends('layouts.admin')
@section('title', 'Blog')
@section('content')

<form method="POST" action="{{ route('admin.blog.store') }}" class="glass-card mb-6 space-y-3 p-5">@csrf
<input name="title" class="input-field" required>
<input name="category" class="input-field">
<textarea name="excerpt" class="input-field"></textarea>
<textarea name="content" class="input-field" rows="6"></textarea>
<button class="btn-brand">Publish post</button>
</form>
@foreach($posts as $p)<div class="glass-card mb-2 p-3 text-sm">{{ $p->title }} · {{ $p->status }}</div>@endforeach{{ $posts->links() }}

@endsection
