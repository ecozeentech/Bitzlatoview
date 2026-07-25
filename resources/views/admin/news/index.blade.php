@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">News</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">New Article</h2>
        <form method="POST" action="{{ route('admin.news.store') }}" class="grid gap-3">
            @csrf
            <input type="text" name="title" class="input-field" placeholder="Title" required>
            <textarea name="summary" class="input-field" rows="3" placeholder="Summary"></textarea>
            <div class="grid gap-3 sm:grid-cols-2">
                <select name="sentiment" class="input-field"><option value="neutral">Neutral</option><option value="bullish">Bullish</option><option value="bearish">Bearish</option></select>
                <input type="text" name="source" class="input-field" placeholder="Source">
            </div>
            <button class="btn-brand">Publish</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Title</th><th>Sentiment</th><th>Published</th><th></th></tr></thead>
            <tbody>
                @foreach ($articles as $a)
                    <tr>
                        <td>{{ $a->title }}</td>
                        <td><span class="pill-{{ $a->sentiment === 'bullish' ? 'success' : ($a->sentiment === 'bearish' ? 'danger' : 'muted') }}">{{ ucfirst($a->sentiment) }}</span></td>
                        <td class="text-text-muted">{{ $a->published_at?->format('M d, Y') }}</td>
                        <td><form method="POST" action="{{ route('admin.news.destroy', $a) }}">@csrf @method('DELETE')<button class="text-xs text-danger hover:underline">Delete</button></form></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
