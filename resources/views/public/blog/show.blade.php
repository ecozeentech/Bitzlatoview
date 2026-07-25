@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12 lg:px-8">
    <span class="pill-info">{{ $post->category }}</span>
    <h1 class="mt-4 text-3xl font-bold">{{ $post->title }}</h1>
    <p class="mt-2 text-sm text-text-muted">{{ $post->author }} · {{ $post->published_at?->format('M j, Y') }}</p>

    <div class="prose prose-invert mt-8 max-w-none text-text-muted">
        {!! $post->content !!}
    </div>

    @if ($related->count())
        <div class="mt-12">
            <h3 class="mb-4 font-semibold">More from the blog</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($related as $item)
                    <a href="{{ route('blog.show', $item->slug) }}" class="glass-card block p-4 text-sm font-medium hover:text-brand">{{ $item->title }}</a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
