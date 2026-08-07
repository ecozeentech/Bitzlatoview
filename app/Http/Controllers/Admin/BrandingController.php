<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BrandingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.branding', ['branding' => BrandingSetting::current()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'max:1024'],
            'favicon' => ['nullable', 'image', 'max:512'],
        ]);

        $branding = BrandingSetting::current();

        if ($request->hasFile('logo')) {
            if ($branding->logo_path) {
                Storage::disk('public')->delete($branding->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($branding->favicon_path) {
                Storage::disk('public')->delete($branding->favicon_path);
            }
            $data['favicon_path'] = $request->file('favicon')->store('branding', 'public');
        }

        unset($data['logo'], $data['favicon']);

        $branding->update($data);
        BrandingSetting::forget();

        AuditLog::record(auth()->user(), 'branding.updated', BrandingSetting::class, $branding->id);

        return back()->with('success', 'Branding updated.');
    }

    public function resetLogo()
    {
        $branding = BrandingSetting::current();
        if ($branding->logo_path) {
            Storage::disk('public')->delete($branding->logo_path);
        }
        $branding->update(['logo_path' => null]);
        BrandingSetting::forget();

        return back()->with('success', 'Logo reset to default.');
    }

    public function resetFavicon()
    {
        $branding = BrandingSetting::current();
        if ($branding->favicon_path) {
            Storage::disk('public')->delete($branding->favicon_path);
        }
        $branding->update(['favicon_path' => null]);
        BrandingSetting::forget();

        return back()->with('success', 'Favicon reset to default.');
    }
}
