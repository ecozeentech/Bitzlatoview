@extends('layouts.admin')
@section('title', 'Users')
@section('content')

<form class="mb-4"><input name="q" value="{{ request('q') }}" class="input-field max-w-sm" placeholder="Search users"></form>
<div class="glass-card overflow-x-auto p-4"><table class="data-table"><thead><tr><th>User</th><th>Role</th><th>KYC</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($users as $u)<tr><td>{{ $u->email }}</td><td>{{ $u->role }}</td><td>{{ $u->kyc_status }}</td><td>{{ $u->status }}</td><td><a class="text-brand" href="{{ route('admin.users.show',$u->id) }}">View</a></td></tr>@endforeach
</tbody></table>{{ $users->links() }}</div>

@endsection
