@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-xl">
    <div class="glass-card p-8">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">Invoice {{ $invoice->invoice_number }}</h1>
            <span class="pill-success">{{ ucfirst($invoice->status) }}</span>
        </div>
        <p class="mt-1 text-sm text-text-muted">Issued {{ $invoice->issued_at->format('M d, Y H:i') }}</p>

        <table class="data-table mt-6">
            <thead><tr><th>Description</th><th>Amount</th></tr></thead>
            <tbody>
                @foreach ($invoice->line_items ?? [] as $item)
                    <tr><td>{{ $item['label'] }}</td><td class="font-numeric">${{ number_format($item['amount'], 2) }}</td></tr>
                @endforeach
            </tbody>
            <tfoot><tr><td class="font-semibold">Total</td><td class="font-numeric font-semibold">${{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</td></tr></tfoot>
        </table>

        <a href="{{ route('app.billing.index') }}" class="btn-outline mt-6 inline-block text-sm">Back to Packages</a>
    </div>
</div>
@endsection
