@extends('layouts.app-shell')
@section('title', 'Settings')
@section('content')
<h1 class="text-2xl font-bold mb-6">Settings</h1><div class="grid gap-3 md:grid-cols-3">@foreach([["Profile","app.settings.profile"],["Security","app.settings.security"],["KYC","app.settings.kyc"],["Notifications","app.settings.notifications"],["API Keys","app.settings.api-keys"],["WalletConnect","app.settings.wallet-connect"]] as [$l,$r])<a href="{{ route($r) }}" class="glass-card p-5">{{ $l }}</a>@endforeach</div>
@endsection
