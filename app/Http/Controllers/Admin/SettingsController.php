<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FeatureFlag;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('key')->get();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'value' => ['nullable', 'string'],
            'type' => ['required', 'in:string,number,boolean,json'],
        ]);

        SystemSetting::updateOrCreate(['key' => $data['key']], ['value' => $data['value'], 'type' => $data['type']]);
        AuditLog::record(auth()->user(), 'system_setting.updated');

        return back()->with('success', 'Setting saved.');
    }

    public function featureFlags()
    {
        $flags = FeatureFlag::orderBy('key')->get();

        return view('admin.settings.feature-flags', compact('flags'));
    }

    public function toggleFlag(FeatureFlag $flag)
    {
        $flag->update(['is_enabled' => ! $flag->is_enabled]);
        AuditLog::record(auth()->user(), 'feature_flag.toggled', FeatureFlag::class, $flag->id);

        return back()->with('success', 'Feature flag updated.');
    }

    public function withdrawalFee()
    {
        $settings = [
            'enabled' => SystemSetting::getValue('withdrawal_fee_enabled', true),
            'percentage' => SystemSetting::getValue('withdrawal_fee_percentage', 0.1),
            'min_fee' => SystemSetting::getValue('withdrawal_fee_min'),
            'max_fee' => SystemSetting::getValue('withdrawal_fee_max'),
        ];

        return view('admin.settings.withdrawal-fee', compact('settings'));
    }

    public function updateWithdrawalFee(Request $request)
    {
        $data = $request->validate([
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_fee' => ['nullable', 'numeric', 'min:0'],
            'max_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_enabled'], ['value' => $request->boolean('enabled') ? '1' : '0', 'type' => 'boolean']);
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_percentage'], ['value' => (string) $data['percentage'], 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_min'], ['value' => $data['min_fee'] !== null ? (string) $data['min_fee'] : null, 'type' => 'number']);
        SystemSetting::updateOrCreate(['key' => 'withdrawal_fee_max'], ['value' => $data['max_fee'] !== null ? (string) $data['max_fee'] : null, 'type' => 'number']);

        AuditLog::record(auth()->user(), 'withdrawal_fee_setting.updated', null, null, null, $data + ['enabled' => $request->boolean('enabled')]);

        return back()->with('success', 'Withdrawal fee settings updated.');
    }

    /**
     * Platform-wide deposit/withdrawal amount floors and ceilings, applied to every user on top
     * of (for deposits) each payment method's own min_amount/max_amount — see
     * App\Http\Controllers\App\FundingController::storeDeposit()/storeWithdraw().
     */
    public function depositWithdrawalLimits()
    {
        $settings = [
            'deposit_min' => SystemSetting::getValue('deposit_min_amount'),
            'deposit_max' => SystemSetting::getValue('deposit_max_amount'),
            'withdrawal_min' => SystemSetting::getValue('withdrawal_min_amount'),
            'withdrawal_max' => SystemSetting::getValue('withdrawal_max_amount'),
        ];

        return view('admin.settings.deposit-withdrawal-limits', compact('settings'));
    }

    public function updateDepositWithdrawalLimits(Request $request)
    {
        $data = $request->validate([
            'deposit_min' => ['nullable', 'numeric', 'min:0'],
            'deposit_max' => ['nullable', 'numeric', 'min:0'],
            'withdrawal_min' => ['nullable', 'numeric', 'min:0'],
            'withdrawal_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (isset($data['deposit_min'], $data['deposit_max']) && $data['deposit_max'] < $data['deposit_min']) {
            return back()->withInput()->with('error', 'The deposit maximum must be greater than or equal to the deposit minimum.');
        }

        if (isset($data['withdrawal_min'], $data['withdrawal_max']) && $data['withdrawal_max'] < $data['withdrawal_min']) {
            return back()->withInput()->with('error', 'The withdrawal maximum must be greater than or equal to the withdrawal minimum.');
        }

        $keys = [
            'deposit_min' => 'deposit_min_amount',
            'deposit_max' => 'deposit_max_amount',
            'withdrawal_min' => 'withdrawal_min_amount',
            'withdrawal_max' => 'withdrawal_max_amount',
        ];

        foreach ($keys as $field => $settingKey) {
            SystemSetting::updateOrCreate(
                ['key' => $settingKey],
                ['value' => isset($data[$field]) && $data[$field] !== '' ? (string) $data[$field] : null, 'type' => 'number']
            );
        }

        AuditLog::record(auth()->user(), 'deposit_withdrawal_limits.updated', null, null, null, $data);

        return back()->with('success', 'Deposit & withdrawal limits updated.');
    }
}
