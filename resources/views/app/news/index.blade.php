@extends('layouts.app-shell')
@section('title', 'News')
@section('content')
<h1 class="text-2xl font-bold mb-6">News</h1><div class="grid gap-4 md:grid-cols-2">@foreach($articles as $a)<div class="glass-card p-4"><h3 class="font-semibold">{{ $a->title }}</h3><p class="text-sm text-muted mt-2">{{ $a->summary }}</p></div>@endforeach</div>{{ $articles->links() }}
@endsection
