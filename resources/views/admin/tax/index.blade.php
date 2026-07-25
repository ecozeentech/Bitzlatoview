@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Tax Reports</h1>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Year</th><th>Method</th><th>Income</th><th>Fees</th><th>Generated</th></tr></thead>
            <tbody>
                @forelse ($reports as $r)
                    <tr>
                        <td>{{ $r->user->email }}</td>
                        <td>{{ $r->year }}</td>
                        <td>{{ strtoupper($r->cost_basis_method) }}</td>
                        <td class="font-numeric">{{ number_format($r->income_total, 4) }}</td>
                        <td class="font-numeric">{{ number_format($r->fees_paid, 4) }}</td>
                        <td class="text-text-muted">{{ $r->generated_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-text-muted">No reports generated yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $reports->links() }}
</div>
@endsection
