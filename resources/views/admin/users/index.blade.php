@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Users</h1>

    <form method="GET" class="flex gap-3">
        <input type="text" name="q" value="{{ request('q') }}" class="input-field" placeholder="Search name or email">
        <select name="kyc_status" class="input-field w-48">
            <option value="">All KYC statuses</option>
            @foreach (['not_started','in_progress','submitted','under_review','approved','rejected','more_info_required'] as $s)
                <option value="{{ $s }}" @selected(request('kyc_status') === $s)>{{ str_replace('_',' ',$s) }}</option>
            @endforeach
        </select>
        <button class="btn-outline">Filter</button>
    </form>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Country</th><th>Role</th><th>KYC</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td class="text-text-muted">{{ $u->email }}</td>
                        <td class="text-text-muted">{{ $u->country }}</td>
                        <td>{{ $u->role }}</td>
                        <td><span class="pill-{{ $u->kyc_status === 'approved' ? 'success' : 'warning' }}">{{ str_replace('_',' ',$u->kyc_status) }}</span></td>
                        <td><span class="pill-{{ $u->status === 'active' ? 'success' : 'danger' }}">{{ $u->status }}</span></td>
                        <td><a href="{{ route('admin.users.show', $u) }}" class="text-xs text-brand hover:underline">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
