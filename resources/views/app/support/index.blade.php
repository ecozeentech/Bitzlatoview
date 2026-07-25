@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Support</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Open a Ticket</h2>
        <form method="POST" action="{{ route('app.support.store') }}" class="space-y-3">
            @csrf
            <input type="text" name="subject" class="input-field" placeholder="Subject" required>
            <select name="category" class="input-field">
                <option value="general">General</option><option value="kyc">KYC</option><option value="deposit">Deposit</option>
                <option value="withdrawal">Withdrawal</option><option value="p2p">P2P</option><option value="trading">Trading</option><option value="card">Card</option>
            </select>
            <textarea name="message" class="input-field" rows="4" placeholder="Describe your issue..." required></textarea>
            <button class="btn-brand">Submit Ticket</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Subject</th><th>Category</th><th>Status</th><th>Priority</th><th></th></tr></thead>
            <tbody>
                @forelse ($tickets as $t)
                    <tr>
                        <td>{{ $t->subject }}</td>
                        <td>{{ ucfirst($t->category) }}</td>
                        <td><span class="pill-{{ $t->status === 'resolved' || $t->status === 'closed' ? 'muted' : 'warning' }}">{{ $t->status }}</span></td>
                        <td>{{ ucfirst($t->priority) }}</td>
                        <td><a href="{{ route('app.support.show', $t) }}" class="text-xs text-brand hover:underline">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-text-muted">No support tickets yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
