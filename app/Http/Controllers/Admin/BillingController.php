<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalystProfile;
use App\Models\BillingPackage;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        $packages = BillingPackage::withCount('subscriptions')->with('analyst')->get();
        $analysts = AnalystProfile::all();

        return view('admin.billing.index', compact('packages', 'analysts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'analyst_profile_id' => ['required', 'exists:analyst_profiles,id'],
            'price' => ['required', 'numeric', 'gt:0'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,yearly'],
            'invoice_label' => ['required', 'string', 'max:150'],
        ]);

        BillingPackage::create($data + [
            'description' => 'Simulated analyst research subscription. Not investment advice.',
            'risk_disclosure' => 'Research and commentary are for informational purposes only and do not constitute investment advice.',
            'report_access' => true,
            'status' => 'active',
        ]);

        return back()->with('success', 'Package created.');
    }

    public function update(Request $request, BillingPackage $package)
    {
        $data = $request->validate(['status' => ['required', 'in:active,archived']]);
        $package->update($data);

        return back()->with('success', 'Package updated.');
    }

    public function verifyAnalyst(AnalystProfile $analyst)
    {
        $analyst->update(['credential_verified' => true]);

        return back()->with('success', 'Analyst credential marked as verified. Ensure legal approval exists before displaying protected designations such as CFA.');
    }
}
