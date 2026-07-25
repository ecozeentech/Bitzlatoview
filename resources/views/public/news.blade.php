@extends('layouts.public')
@section('title', 'News')
@section('content')

<div class="page-shell py-16"><h1 class="section-title">Crypto News</h1>
<div class="mt-8 grid gap-4 md:grid-cols-3">
@foreach($articles as $a)
<article class="glass-card p-5"><span class="badge-muted">{{ ucfirst($a->sentiment) }}</span><h3 class="mt-3 font-semibold">{{ $a->title }}</h3><p class="mt-2 text-sm text-muted">{{ $a->summary }}</p></article>
@endforeach
</div><div class="mt-6">{{ $articles->links() }}</div></div>
@endsection
