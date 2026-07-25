<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ComplianceAlert;
use App\Models\RiskScore;

class RiskController extends Controller
{
    public function index()
    {
        $alerts = ComplianceAlert::with('user')->where('status', 'open')->latest()->get();
        $riskScores = RiskScore::with('user')->where('level', '!=', 'low')->latest()->take(20)->get();

        return view('admin.risk.index', compact('alerts', 'riskScores'));
    }

    public function resolveAlert(ComplianceAlert $alert)
    {
        $alert->update(['status' => 'resolved', 'resolved_by' => auth()->id(), 'resolved_at' => now()]);
        AuditLog::record(auth()->user(), 'compliance_alert.resolved', ComplianceAlert::class, $alert->id);

        return back()->with('success', 'Alert resolved.');
    }
}
