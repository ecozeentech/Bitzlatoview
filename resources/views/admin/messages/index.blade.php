@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ recipient: 'single' }">
    <h1 class="text-2xl font-bold">Messages to Users</h1>
    <p class="text-sm text-text-muted">Send a message that appears in the user's notification bell (Recent Activity dropdown) on their dashboard.</p>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Compose Message</h2>
        <form method="POST" action="{{ route('admin.messages.store') }}" class="grid gap-3 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2 flex gap-4 text-sm">
                <label class="flex items-center gap-2"><input type="radio" name="recipient" value="single" x-model="recipient" class="text-brand focus:ring-brand"> Specific user</label>
                <label class="flex items-center gap-2"><input type="radio" name="recipient" value="all" x-model="recipient" class="text-brand focus:ring-brand"> All users (broadcast)</label>
            </div>
            <div x-show="recipient === 'single'" class="sm:col-span-2">
                <label class="label-field">User email</label>
                <input type="email" name="user_email" class="input-field" placeholder="user@example.com">
            </div>
            <div class="sm:col-span-2">
                <label class="label-field">Title</label>
                <input type="text" name="title" class="input-field" required>
            </div>
            <div class="sm:col-span-2">
                <label class="label-field">Message</label>
                <textarea name="body" class="input-field" rows="3" required></textarea>
            </div>
            <button class="btn-brand sm:col-span-2">Send Message</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <h2 class="p-5 pb-0 font-semibold">Recent Messages</h2>
        <div class="overflow-x-auto p-5 pt-3">
            <table class="data-table">
                <thead><tr><th>To</th><th>Title</th><th>Sent</th><th>Read</th></tr></thead>
                <tbody>
                    @forelse ($messages as $m)
                        <tr>
                            <td>{{ $m->user->email }}</td>
                            <td>{{ $m->title }}</td>
                            <td class="text-text-muted">{{ $m->created_at->diffForHumans() }}</td>
                            <td><span class="pill-{{ $m->read_at ? 'success' : 'muted' }}">{{ $m->read_at ? 'Read' : 'Unread' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-text-muted">No messages sent yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
