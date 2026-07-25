@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
@foreach([['Users',$totalUsers],['New 7d',$newUsers],['KYC pending',$kycPending],['Pending withdrawals',$pendingWithdrawals],['P2P disputes',$p2pDisputes],['Active bots',$activeBots],['Mining contracts',$activeMining],['Support open',$supportTickets]] as [$l,$v])
<div class="glass-card p-5"><p class="text-xs text-muted">{{ $l }}</p><p class="stat-value mt-2">{{ $v }}</p></div>
@endforeach
</div>
<div class="mt-6 grid gap-4 lg:grid-cols-2">
<div class="glass-card p-5"><h3 class="font-semibold mb-3">Volumes</h3><p class="text-sm">Deposits: {{ number_format($depositVolume,2) }}</p><p class="text-sm">Withdrawals: {{ number_format($withdrawalVolume,2) }}</p><p class="text-sm">Emails (24h): {{ $emailSent }}</p><p class="text-sm">Risk alerts: {{ $riskAlerts }}</p></div>
<div class="glass-card p-5"><h3 class="font-semibold mb-3">Recent audit</h3>@foreach($recentAudits as $a)<div class="border-b border-border/40 py-2 text-xs">{{ $a->action }} · user {{ $a->actor_id }}</div>@endforeach</div>
</div>

@endsection
