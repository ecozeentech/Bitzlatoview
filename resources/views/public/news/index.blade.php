@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-12 lg:px-8">
    <h1 class="text-3xl font-bold">Crypto News</h1>
    <p class="mt-2 text-text-muted">Simulated market news for demonstration purposes — not financial advice.</p>

    <div class="mt-8 space-y-4">
        @foreach ($articles as $article)
            <div class="glass-card p-5">
                <div class="flex items-center gap-2 text-xs">
                    <span class="pill-{{ $article->sentiment === 'bullish' ? 'success' : ($article->sentiment === 'bearish' ? 'danger' : 'muted') }}">{{ ucfirst($article->sentiment) }}</span>
                    <span class="text-text-muted">{{ $article->source }} · {{ $article->published_at?->diffForHumans() }}</span>
                </div>
                <h2 class="mt-2 text-lg font-semibold">{{ $article->title }}</h2>
                <p class="mt-1 text-sm text-text-muted">{{ $article->summary }}</p>
                @if (!empty($article->related_assets))
                    <div class="mt-3 flex gap-2">
                        @foreach ($article->related_assets as $asset)
                            <span class="pill-muted">{{ $asset }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-8">{{ $articles->links() }}</div>
</div>
@endsection
