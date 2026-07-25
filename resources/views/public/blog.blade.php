@extends('layouts.public')
@section('title', 'Blog')
@section('content')

<div class="page-shell py-16"><h1 class="section-title">Blog</h1>
<div class="mt-8 grid gap-4 md:grid-cols-3">
@foreach($posts as $p)
<a href="{{ route('blog.show',$p->slug) }}" class="glass-card block p-5"><h3 class="font-semibold">{{ $p->title }}</h3><p class="mt-2 text-sm text-muted">{{ $p->excerpt }}</p></a>
@endforeach
</div><div class="mt-6">{{ $posts->links() }}</div></div>
@endsection
