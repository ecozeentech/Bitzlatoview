@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-16 lg:px-8">
    <span class="pill-info">{{ $page['tag'] }}</span>
    <h1 class="mt-4 text-4xl font-extrabold">{{ $page['title'] }}</h1>
    <p class="mt-4 max-w-2xl text-lg text-text-muted">{{ $page['subtitle'] }}</p>

    <div class="mt-8 flex gap-4">
        <a href="{{ auth()->check() ? $page['cta'][1] : route('register') }}" class="btn-brand">{{ $page['cta'][0] }}</a>
        <a href="/markets" class="btn-outline">View Markets</a>
    </div>

    <div class="mt-12 grid gap-4 sm:grid-cols-2">
        @foreach ($page['features'] as $feature)
            <div class="glass-card p-4 text-sm text-text-muted">✓ {{ $feature }}</div>
        @endforeach
    </div>

    <div class="risk-banner mt-10">
        <strong class="text-text-main">Risk disclosure:</strong> {{ $page['risk'] }}
    </div>
</div>
@endsection
