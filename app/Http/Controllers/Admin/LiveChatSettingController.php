<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LiveChatSetting;
use Illuminate\Http\Request;

class LiveChatSettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.live-chat', ['setting' => LiveChatSetting::current()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'property_id' => ['nullable', 'string', 'max:100'],
            'widget_id' => ['nullable', 'string', 'max:100'],
        ]);
        $data['is_enabled'] = $request->boolean('is_enabled');

        $setting = LiveChatSetting::current();
        $setting->update($data);

        AuditLog::record(auth()->user(), 'live_chat_setting.updated');

        return back()->with('success', 'Live chat settings updated.');
    }
}
