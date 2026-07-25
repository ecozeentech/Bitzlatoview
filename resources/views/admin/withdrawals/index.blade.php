@extends('layouts.admin')
@section('title', 'Withdrawals')
@section('content')
@foreach($withdrawals as $w)<div class="glass-card mb-3 flex flex-wrap justify-between gap-3 p-4"><div class="text-sm">{{ $w->uuid }} · {{ $w->amount }} · {{ $w->status }} · {{ $w->destination_address }}</div>@if($w->status==='pending_review')<form method="POST" action="{{ route('admin.withdrawals.action',$w->id) }}" class="flex gap-2">@csrf @method('PATCH')<button name="action" value="approve" class="btn-brand text-xs">Approve</button><button name="action" value="reject" class="btn-outline text-xs">Reject</button></form>@endif</div>@endforeach{{ $withdrawals->links() }}
@endsection
