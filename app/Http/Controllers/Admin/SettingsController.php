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
}
