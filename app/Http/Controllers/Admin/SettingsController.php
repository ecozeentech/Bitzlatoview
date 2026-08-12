<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\App\CopyTradingController;
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
        $copyTradingMinAmount = CopyTradingController::globalMinimumAmount();

        return view('admin.settings.index', compact('settings', 'copyTradingMinAmount'));
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

    public function updateCopyTradingMinAmount(Request $request)
    {
        $data = $request->validate([
            'copy_trading_min_amount' => ['required', 'numeric', 'min:0'],
        ]);

        SystemSetting::updateOrCreate(
            ['key' => CopyTradingController::minAmountSettingKey()],
            ['value' => $data['copy_trading_min_amount'], 'type' => 'number']
        );
        AuditLog::record(auth()->user(), 'copy_trading.min_amount_updated');

        return back()->with('success', 'Copy trading minimum investment amount updated.');
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
}
