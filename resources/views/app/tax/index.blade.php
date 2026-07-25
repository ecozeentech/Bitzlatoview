@extends('layouts.app-shell')
@section('title', 'Tax Center')
@section('content')

<h1 class="text-2xl font-bold mb-2">Tax Center</h1>
<p class="risk-banner mb-6">This is not tax advice. Export helpers are informational only.</p>
<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
@foreach([['Realized gains',$report->realized_gains],['Realized losses',$report->realized_losses],['Income',$report->income_total],['Fees paid',$report->fees_paid]] as [$l,$v])
<div class="glass-card p-5"><p class="text-xs text-muted">{{ $l }}</p><p class="stat-value mt-2">${{ number_format($v,2) }}</p></div>
@endforeach
</div>
<div class="glass-card mt-6 p-5 text-sm space-y-2">
<p>Year: {{ $report->tax_year }} · Country: {{ $report->country }}</p>
<p>Cost basis method: {{ $report->cost_basis_method }} (FIFO / LIFO / HIFO supported in settings)</p>
<p>Status: {{ $report->status }}</p>
<button class="btn-outline text-xs mt-3" disabled>Export CSV (connect tax provider)</button>
</div>

@endsection
