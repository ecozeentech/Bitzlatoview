@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Tax Center</h1>
    <div class="risk-banner">This is not tax advice. Consult a qualified tax professional for your jurisdiction.</div>

    <form method="GET" class="flex items-center gap-3">
        <select name="year" onchange="this.form.submit()" class="input-field w-32">
            @foreach ($years as $y)
                <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
            @endforeach
        </select>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="glass-card p-5"><p class="text-xs text-text-muted">Mining Income</p><p class="font-numeric text-xl font-bold price-up">{{ number_format($miningIncome, 8) }}</p></div>
        <div class="glass-card p-5"><p class="text-xs text-text-muted">Investment/Staking Income</p><p class="font-numeric text-xl font-bold price-up">{{ number_format($investmentIncome, 8) }}</p></div>
        <div class="glass-card p-5"><p class="text-xs text-text-muted">Trading Fees Paid</p><p class="font-numeric text-xl font-bold">{{ number_format($tradingFees, 4) }}</p></div>
        <div class="glass-card p-5"><p class="text-xs text-text-muted">Swap Fees Paid</p><p class="font-numeric text-xl font-bold">{{ number_format($swapFees, 4) }}</p></div>
    </div>

    <div class="glass-card p-6">
        <h2 class="mb-3 font-semibold">Generate Report</h2>
        <form method="POST" action="{{ route('app.tax.reports.generate') }}" class="grid gap-3 sm:grid-cols-4">
            @csrf
            <input type="number" name="year" value="{{ $year }}" class="input-field" required>
            <input type="text" name="country" value="{{ auth()->user()->country }}" class="input-field" placeholder="Country">
            <select name="cost_basis_method" class="input-field">
                <option value="fifo">FIFO</option><option value="lifo">LIFO</option><option value="hifo">HIFO</option><option value="average">Average Cost</option>
            </select>
            <button class="btn-brand">Generate</button>
        </form>
    </div>

    <div class="glass-card p-5">
        <h2 class="mb-3 font-semibold">Report History</h2>
        <table class="data-table">
            <thead><tr><th>Year</th><th>Method</th><th>Income</th><th>Fees</th><th>Generated</th><th></th></tr></thead>
            <tbody>
                @forelse ($reports as $r)
                    <tr>
                        <td>{{ $r->year }}</td>
                        <td>{{ strtoupper($r->cost_basis_method) }}</td>
                        <td class="font-numeric">{{ number_format($r->income_total, 4) }}</td>
                        <td class="font-numeric">{{ number_format($r->fees_paid, 4) }}</td>
                        <td class="text-text-muted">{{ $r->generated_at?->format('M d, Y') }}</td>
                        <td><a href="{{ route('app.tax.reports.export', $r) }}" class="text-xs text-brand hover:underline">Export CSV</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No reports generated yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
