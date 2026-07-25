@extends('layouts.admin')
@section('title', 'KYC Queue')
@section('content')

<div class="space-y-4">
@foreach($submissions as $s)
<div class="glass-card p-5">
  <div class="flex flex-wrap justify-between gap-3"><div><p class="font-semibold">{{ $s->legal_name }}</p><p class="text-sm text-muted">{{ $s->user->email ?? '' }} · {{ $s->status }}</p></div>
  <form method="POST" action="{{ route('admin.kyc.review',$s->id) }}" class="flex flex-wrap gap-2">@csrf @method('PATCH')
  <input type="hidden" name="action" id="action-{{ $s->id }}">
  <input name="admin_note" class="input-field !w-40" placeholder="Note">
  <button class="btn-brand text-xs" onclick="document.getElementById('action-{{ $s->id }}').value='approve'">Approve</button>
  <button class="btn-outline text-xs" onclick="document.getElementById('action-{{ $s->id }}').value='more_info'">More info</button>
  <button class="btn-ghost text-xs text-danger" onclick="document.getElementById('action-{{ $s->id }}').value='reject'">Reject</button>
  </form></div>
</div>
@endforeach
{{ $submissions->links() }}
</div>

@endsection
