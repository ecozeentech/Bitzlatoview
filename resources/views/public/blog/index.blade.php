@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-12 lg:px-8">
    <h1 class="text-3xl font-bold">Bitzlatoview Blog</h1>
    <p class="mt-2 text-text-muted">Guides, education and product updates.</p>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="glass-card block p-5">
                <span class="pill-info">{{ $post->category }}</span>
                <h2 class="mt-3 font-semibold">{{ $post->title }}</h2>
                <p class="mt-2 text-sm text-text-muted">{{ $post->excerpt }}</p>
                <p class="mt-3 text-xs text-text-muted">{{ $post->author }} · {{ $post->published_at?->format('M j, Y') }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-8">{{ $posts->links() }}</div>
</div>
@endsection
