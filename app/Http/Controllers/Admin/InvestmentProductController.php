<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Asset;
use App\Models\InvestmentProduct;
use Illuminate\Http\Request;

class InvestmentProductController extends Controller
{
    public function index()
    {
        $products = InvestmentProduct::withCount('subscriptions')->with('asset')->latest()->get();
        $assets = Asset::orderBy('symbol')->get();

        return view('admin.investments.index', compact('products', 'assets'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $product = InvestmentProduct::create($data);

        AuditLog::record(auth()->user(), 'investment_product.created', InvestmentProduct::class, $product->id);

        return back()->with('success', "Investment product \"{$product->name}\" created.");
    }

    public function update(Request $request, InvestmentProduct $product)
    {
        $data = $this->validated($request);
        $product->update($data);

        AuditLog::record(auth()->user(), 'investment_product.updated', InvestmentProduct::class, $product->id);

        return back()->with('success', "Investment product \"{$product->name}\" updated.");
    }

    public function toggle(InvestmentProduct $product)
    {
        $product->update(['status' => $product->status === 'active' ? 'paused' : 'active']);

        AuditLog::record(auth()->user(), 'investment_product.status_toggled', InvestmentProduct::class, $product->id);

        return back()->with('success', "Investment product is now {$product->status}.");
    }

    public function destroy(InvestmentProduct $product)
    {
        if ($product->subscriptions()->exists()) {
            return back()->with('error', 'Cannot delete a product with existing subscriptions — pause it instead.');
        }

        AuditLog::record(auth()->user(), 'investment_product.deleted', InvestmentProduct::class, $product->id);
        $product->delete();

        return back()->with('success', 'Investment product deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'asset_id' => ['required', 'exists:assets,id'],
            'apy_pct' => ['required', 'numeric', 'min:0', 'max:1000'],
            'risk_level' => ['required', 'in:low,moderate,high'],
            'lock_days' => ['required', 'integer', 'min:0'],
            'payout_frequency' => ['required', 'in:daily,weekly'],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'status' => ['required', 'in:active,paused'],
        ]);
    }
}
