@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Platform Settings</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Copy Trading — Global Minimum Investment</h2>
        <p class="mb-3 text-sm text-text-muted">Every user's chosen "minimum investment amount" when copying a trader must be at least this platform-wide floor.</p>
        <form method="POST" action="{{ route('admin.settings.copy-trading-min-amount') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="label-field">Global minimum (USDT)</label>
                <input type="number" step="0.01" min="0" name="copy_trading_min_amount" value="{{ $copyTradingMinAmount }}" class="input-field w-48" required>
            </div>
            <button class="btn-brand text-sm">Save</button>
        </form>
    </div>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-3 sm:grid-cols-3">
            @csrf
            <input type="text" name="key" class="input-field" placeholder="Setting key" required>
            <input type="text" name="value" class="input-field" placeholder="Value">
            <select name="type" class="input-field"><option value="string">String</option><option value="number">Number</option><option value="boolean">Boolean</option><option value="json">JSON</option></select>
            <button class="btn-brand sm:col-span-3">Save Setting</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Key</th><th>Value</th><th>Type</th></tr></thead>
            <tbody>
                @forelse ($settings as $s)
                    <tr><td>{{ $s->key }}</td><td class="text-text-muted">{{ $s->value }}</td><td>{{ $s->type }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-text-muted">No custom settings yet — platform defaults apply.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('admin.settings.feature-flags') }}" class="btn-outline text-sm">Manage Feature Flags</a>
</div>
@endsection
