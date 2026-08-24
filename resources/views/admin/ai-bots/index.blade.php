@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ edit: null }">
    <h1 class="text-2xl font-bold">AI Bot Strategies</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Create Bot</h2>
        <form method="POST" action="{{ route('admin.ai-bots.store') }}" class="grid gap-3 sm:grid-cols-4">
            @csrf
            @include('admin.ai-bots._fields')
            <button class="btn-brand sm:col-span-4">Create</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Strategy</th><th>Min. Amount</th><th>Lock</th><th>Allocations</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($bots as $bot)
                    <tr>
                        <td>{{ $bot->name }}</td>
                        <td>{{ ucfirst($bot->strategy_type) }}</td>
                        <td class="font-numeric">${{ number_format($bot->min_allocation, 2) }}</td>
                        <td>{{ $bot->lock_days }}d</td>
                        <td class="font-numeric">{{ $bot->allocations_count }}</td>
                        <td><span class="pill-{{ $bot->status === 'active' ? 'success' : ($bot->status === 'sold_out' ? 'warning' : 'muted') }}">{{ str_replace('_', ' ', $bot->status) }}</span></td>
                        <td class="space-x-2 whitespace-nowrap">
                            <button type="button" @click="edit = edit === {{ $bot->id }} ? null : {{ $bot->id }}" class="text-xs text-brand hover:underline">Edit</button>
                            <form method="POST" action="{{ route('admin.ai-bots.toggle-availability', $bot) }}" class="inline">
                                @csrf
                                <button class="text-xs text-brand hover:underline">{{ $bot->status === 'sold_out' ? 'Mark Available' : 'Mark Sold Out' }}</button>
                            </form>
                        </td>
                    </tr>
                    <tr x-show="edit === {{ $bot->id }}" x-cloak>
                        <td colspan="7" class="bg-surface-2/40 p-4">
                            <form method="POST" action="{{ route('admin.ai-bots.update', $bot) }}" class="grid gap-3 sm:grid-cols-4">
                                @csrf @method('PATCH')
                                @include('admin.ai-bots._fields', ['bot' => $bot])
                                <button class="btn-brand sm:col-span-4">Save Changes</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
