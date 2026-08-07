<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.payment-methods.index', compact('methods'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('qr_code')) {
            $data['qr_code_path'] = $request->file('qr_code')->store('payment-method-qr', 'public');
        }

        $method = PaymentMethod::create($data);

        AuditLog::record(auth()->user(), 'payment_method.created', PaymentMethod::class, $method->id, null, $data);

        return back()->with('success', 'Payment method added.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $this->validated($request);

        if ($request->hasFile('qr_code')) {
            if ($paymentMethod->qr_code_path) {
                Storage::disk('public')->delete($paymentMethod->qr_code_path);
            }
            $data['qr_code_path'] = $request->file('qr_code')->store('payment-method-qr', 'public');
        }

        $before = $paymentMethod->only(array_keys($data));
        $paymentMethod->update($data);

        AuditLog::record(auth()->user(), 'payment_method.updated', PaymentMethod::class, $paymentMethod->id, $before, $data);

        return back()->with('success', 'Payment method updated.');
    }

    public function toggle(PaymentMethod $paymentMethod)
    {
        $paymentMethod->update(['is_active' => ! $paymentMethod->is_active]);
        AuditLog::record(auth()->user(), 'payment_method.toggled', PaymentMethod::class, $paymentMethod->id);

        return back()->with('success', 'Payment method '.($paymentMethod->is_active ? 'activated' : 'deactivated').'.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->deposits()->exists()) {
            return back()->with('error', 'This payment method has deposit history and cannot be deleted — deactivate it instead.');
        }

        if ($paymentMethod->qr_code_path) {
            Storage::disk('public')->delete($paymentMethod->qr_code_path);
        }

        AuditLog::record(auth()->user(), 'payment_method.deleted', PaymentMethod::class, $paymentMethod->id);
        $paymentMethod->delete();

        return back()->with('success', 'Payment method deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:'.implode(',', PaymentMethod::TYPES)],
            'currency' => ['required', 'string', 'max:16'],
            'network' => ['nullable', 'string', 'max:100'],
            'instructions' => ['required', 'string', 'max:5000'],
            'address' => ['nullable', 'string', 'max:255'],
            'memo' => ['nullable', 'string', 'max:255'],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'sort_order' => ['nullable', 'integer'],
            'qr_code' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
