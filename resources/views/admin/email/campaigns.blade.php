@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Email Campaigns</h1>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Draft Campaign</h2>
        <form method="POST" action="{{ route('admin.email.campaigns.store') }}" class="space-y-3">
            @csrf
            <input type="text" name="name" class="input-field" placeholder="Campaign name" required>
            <input type="text" name="subject" class="input-field" placeholder="Subject line" required>
            <select name="segment" class="input-field">
                <option value="all_users">All users</option>
                <option value="verified_users">Verified users</option>
                <option value="active_traders">Active traders</option>
            </select>
            <textarea name="body_html" class="input-field" rows="5" placeholder="HTML body" required></textarea>
            <button class="btn-brand">Save Draft</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Segment</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($campaigns as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td class="text-text-muted">{{ str_replace('_',' ',$c->segment) }}</td>
                        <td><span class="pill-{{ $c->status === 'sent' ? 'success' : 'warning' }}">{{ $c->status }}</span></td>
                        <td class="space-x-2">
                            <form method="POST" action="{{ route('admin.email.campaigns.send-test', $c) }}" class="inline-flex gap-1">
                                @csrf
                                <input type="email" name="test_email" class="input-field w-40 text-xs" placeholder="test@email.com" required>
                                <button class="text-xs text-brand hover:underline">Send Test</button>
                            </form>
                            @if ($c->status !== 'sent')
                                <form method="POST" action="{{ route('admin.email.campaigns.send', $c) }}" class="inline">@csrf<button class="text-xs text-brand hover:underline">Send Now</button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
