@extends('layouts.admin')
@section('title', 'User')
@section('content')

<h2 class="text-xl font-bold mb-4">{{ $user->email }}</h2>
<form method="POST" action="{{ route('admin.users.update',$user->id) }}" class="glass-card mb-6 grid gap-3 p-5 md:grid-cols-2">@csrf @method('PATCH')
<select name="status" class="input-field"><option @selected($user->status==='active')>active</option><option @selected($user->status==='suspended')>suspended</option></select>
<select name="role" class="input-field">@foreach(['user','admin','support','compliance'] as $r)<option @selected($user->role===$r)>{{ $r }}</option>@endforeach</select>
<select name="kyc_status" class="input-field">@foreach(['not_started','in_progress','submitted','under_review','approved','rejected','more_info_required'] as $k)<option @selected($user->kyc_status===$k)>{{ $k }}</option>@endforeach</select>
<textarea name="admin_note" class="input-field md:col-span-2" placeholder="Admin note"></textarea>
<button class="btn-brand md:col-span-2">Save</button>
</form>
<div class="glass-card p-4"><h3 class="font-semibold mb-3">Wallets (readonly)</h3>@foreach($user->walletAccounts as $w)<div class="mb-3"><p class="text-brand text-sm">{{ $w->type }}</p>@foreach($w->balances as $b)<p class="font-mono text-xs">{{ $b->asset->symbol }} avail {{ $b->available }} locked {{ $b->locked }}</p>@endforeach</div>@endforeach</div>

@endsection
