@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8" x-data="{ open: null }">
    <h1 class="text-3xl font-bold">Frequently Asked Questions</h1>

    @foreach ($faqs as $category => $items)
        <h2 class="mb-3 mt-8 text-lg font-semibold capitalize">{{ str_replace('_', ' ', $category) }}</h2>
        <div class="space-y-3">
            @foreach ($items as $faq)
                <div class="glass-card overflow-hidden">
                    <button class="flex w-full items-center justify-between p-4 text-left font-medium" @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}">
                        {{ $faq->question }}
                        <svg class="h-4 w-4 shrink-0 transition" :class="{ 'rotate-180': open === {{ $faq->id }} }" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="open === {{ $faq->id }}" x-transition class="px-4 pb-4 text-sm text-text-muted">{{ $faq->answer }}</div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
@endsection
