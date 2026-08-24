@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{ edit: null }">
    <h1 class="text-2xl font-bold">Tax Reports</h1>
    <p class="text-xs text-text-muted">Set a tax rate and estimated amount owed per report — this is informational for the user, not a substitute for professional tax advice, and does not affect any ledger balance.</p>
    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>User</th><th>Year</th><th>Method</th><th>Income</th><th>Fees</th><th>Tax Rate</th><th>Est. Tax Owed</th><th>Generated</th><th></th></tr></thead>
            <tbody>
                @forelse ($reports as $r)
                    <tr>
                        <td>{{ $r->user->email }}</td>
                        <td>{{ $r->year }}</td>
                        <td>{{ strtoupper($r->cost_basis_method) }}</td>
                        <td class="font-numeric">{{ number_format($r->income_total, 4) }}</td>
                        <td class="font-numeric">{{ number_format($r->fees_paid, 4) }}</td>
                        <td class="font-numeric">{{ $r->tax_rate_pct !== null ? number_format($r->tax_rate_pct, 2).'%' : '—' }}</td>
                        <td class="font-numeric">{{ $r->estimated_tax_owed !== null ? number_format($r->estimated_tax_owed, 2) : '—' }}</td>
                        <td class="text-text-muted">{{ $r->generated_at?->format('M d, Y') }}</td>
                        <td><button type="button" @click="edit = edit === {{ $r->id }} ? null : {{ $r->id }}" class="text-xs text-brand hover:underline">Edit</button></td>
                    </tr>
                    <tr x-show="edit === {{ $r->id }}" x-cloak>
                        <td colspan="9" class="bg-surface-2/40 p-4">
                            <form method="POST" action="{{ route('admin.tax.update', $r) }}" class="grid gap-3 sm:grid-cols-3">
                                @csrf @method('PATCH')
                                <div>
                                    <label class="label-field">Tax rate (%)</label>
                                    <input type="number" step="0.01" name="tax_rate_pct" class="input-field" value="{{ $r->tax_rate_pct }}">
                                </div>
                                <div>
                                    <label class="label-field">Estimated tax owed</label>
                                    <input type="number" step="0.01" name="estimated_tax_owed" class="input-field" value="{{ $r->estimated_tax_owed }}">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="label-field">Admin notes (internal, not shown to user)</label>
                                    <textarea name="admin_notes" class="input-field" rows="2">{{ $r->admin_notes }}</textarea>
                                </div>
                                <button class="btn-brand text-sm sm:col-span-3 w-fit">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-text-muted">No reports generated yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $reports->links() }}
</div>
@endsection
