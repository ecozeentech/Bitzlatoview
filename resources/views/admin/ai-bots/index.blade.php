@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">AI Bot Strategies</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Create Bot</h2>
        <form method="POST" action="{{ route('admin.ai-bots.store') }}" class="grid gap-3 sm:grid-cols-4">
            @csrf
            <input type="text" name="name" class="input-field" placeholder="Name" required>
            <select name="strategy_type" class="input-field">
                @foreach (['conservative','balanced','aggressive','grid','dca','trend','arbitrage'] as $s)
                    <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <input type="number" name="risk_score" class="input-field" placeholder="Risk score" required>
            <input type="number" step="0.01" name="min_allocation" class="input-field" placeholder="Min allocation" required>
            <input type="number" step="0.1" name="historical_return_pct" class="input-field" placeholder="Historical return %" required>
            <input type="number" step="0.1" name="max_drawdown_pct" class="input-field" placeholder="Max drawdown %" required>
            <input type="number" name="lock_days" class="input-field" placeholder="Lock days" required>
            <button class="btn-brand">Create</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Strategy</th><th>Allocations</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($bots as $bot)
                    <tr>
                        <td>{{ $bot->name }}</td>
                        <td>{{ ucfirst($bot->strategy_type) }}</td>
                        <td class="font-numeric">{{ $bot->allocations_count }}</td>
                        <td><span class="pill-{{ $bot->status === 'active' ? 'success' : 'muted' }}">{{ $bot->status }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.ai-bots.update', $bot) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $bot->status === 'active' ? 'paused' : 'active' }}">
                                <button class="text-xs text-brand hover:underline">{{ $bot->status === 'active' ? 'Pause Globally' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
