@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Crypto News</h1>
    <p class="text-sm text-text-muted">Market commentary — not financial advice.</p>

    <div class="space-y-4">
        @foreach ($articles as $article)
            <div class="glass-card p-5">
                <div class="flex items-center gap-2 text-xs">
                    <span class="pill-{{ $article->sentiment === 'bullish' ? 'success' : ($article->sentiment === 'bearish' ? 'danger' : 'muted') }}">{{ ucfirst($article->sentiment) }}</span>
                    <span class="text-text-muted">{{ $article->source }} · {{ $article->published_at?->diffForHumans() }}</span>
                </div>
                <h2 class="mt-2 text-lg font-semibold">{{ $article->title }}</h2>
                <p class="mt-1 text-sm text-text-muted">{{ $article->summary }}</p>
            </div>
        @endforeach
    </div>

    <div>{{ $articles->links() }}</div>
</div>
@endsection
