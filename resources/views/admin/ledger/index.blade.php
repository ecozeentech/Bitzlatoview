@extends('layouts.admin')
@section('title', 'Ledger')
@section('content')
@foreach($transactions as $t)<div class="glass-card mb-3 p-4"><p class="font-semibold text-sm">{{ $t->type }} · {{ $t->uuid }}</p><p class="text-xs text-muted">{{ $t->description }} · {{ $t->idempotency_key }}</p>@foreach($t->entries as $e)<p class="font-mono text-xs">{{ $e->entry_type }} {{ $e->amount }} {{ $e->balance_bucket }}</p>@endforeach</div>@endforeach{{ $transactions->links() }}
@endsection
