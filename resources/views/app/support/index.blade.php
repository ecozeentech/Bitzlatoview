@extends('layouts.app-shell')
@section('title', 'Support')
@section('content')
<h1 class="text-2xl font-bold mb-6">Support</h1><form method="POST" action="{{ route('app.support.open') }}" class="glass-card mb-6 space-y-3 p-5 max-w-xl">@csrf<input name="subject" class="input-field" placeholder="Subject" required><select name="category" class="input-field"><option>general</option><option>funding</option><option>trading</option><option>p2p</option><option>kyc</option></select><textarea name="body" class="input-field" rows="4" required></textarea><button class="btn-brand">Open ticket</button></form>@foreach($tickets as $t)<div class="glass-card mb-2 p-3 text-sm">{{ $t->subject }} · {{ $t->status }}</div>@endforeach
@endsection
