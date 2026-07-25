<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TraderProfile;
use Illuminate\Http\Request;

class CopyTradingController extends Controller
{
    public function index()
    {
        $traders = TraderProfile::withCount('allocations')->latest()->get();

        return view('admin.copy-trading.index', compact('traders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'in:crypto,forex,futures,stock,p2p'],
            'risk_score' => ['required', 'integer', 'min:1', 'max:100'],
            'return_30d_pct' => ['required', 'numeric'],
            'return_90d_pct' => ['required', 'numeric'],
            'max_drawdown_pct' => ['required', 'numeric'],
            'win_rate_pct' => ['required', 'numeric'],
            'strategy' => ['nullable', 'string', 'max:2000'],
        ]);

        TraderProfile::create($data + ['status' => 'active', 'bio' => 'Simulated performance for demonstration purposes.']);

        return back()->with('success', 'Demo trader created.');
    }

    public function update(Request $request, TraderProfile $trader)
    {
        $data = $request->validate([
            'is_verified' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'status' => ['required', 'in:active,suspended,pending_approval'],
        ]);

        $trader->update($data);
        AuditLog::record(auth()->user(), 'trader.updated', TraderProfile::class, $trader->id);

        return back()->with('success', 'Trader updated.');
    }
}
