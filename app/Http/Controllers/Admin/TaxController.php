<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TaxReport;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        $reports = TaxReport::with('user')->latest()->paginate(30);

        return view('admin.tax.index', compact('reports'));
    }

    public function update(Request $request, TaxReport $report)
    {
        $data = $request->validate([
            'tax_rate_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'estimated_tax_owed' => ['nullable', 'numeric', 'min:0'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $before = $report->only(array_keys($data));
        $report->update($data);

        AuditLog::record(auth()->user(), 'tax_report.updated', TaxReport::class, $report->id, $before, $data);

        return back()->with('success', "Tax report for {$report->user->email} ({$report->year}) updated.");
    }
}
