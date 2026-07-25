@extends('layouts.app-shell')
@section('title', 'Blog')
@section('content')
<h1 class="text-2xl font-bold mb-6">Blog</h1>@foreach($posts as $p)<div class="glass-card mb-3 p-4"><h3 class="font-semibold">{{ $p->title }}</h3><p class="text-sm text-muted">{{ $p->excerpt }}</p></div>@endforeach{{ $posts->links() }}
@endsection
