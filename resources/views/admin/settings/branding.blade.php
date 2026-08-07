@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">Branding</h1>
    <p class="text-sm text-text-muted">Upload a custom logo and favicon — they're used across the public site, user app, and admin dashboard. Leave blank to keep the default Bitzlatoview wordmark.</p>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('admin.settings.branding.update') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="label-field">Site name</label>
                <input type="text" name="site_name" class="input-field" value="{{ old('site_name', $branding->site_name) }}" required>
            </div>

            <div>
                <label class="label-field">Logo</label>
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-lg border border-border bg-surface-2">
                        @if ($branding->logoUrl())
                            <img src="{{ $branding->logoUrl() }}" class="max-h-12 max-w-12" alt="Current logo">
                        @else
                            <span class="text-xs text-text-muted">None</span>
                        @endif
                    </div>
                    <input type="file" name="logo" accept="image/*" class="input-field flex-1">
                </div>
                @if ($branding->logo_path)
                    <form method="POST" action="{{ route('admin.settings.branding.reset-logo') }}" class="mt-2">
                        @csrf
                        <button class="text-xs text-danger hover:underline">Reset to default</button>
                    </form>
                @endif
            </div>

            <div>
                <label class="label-field">Favicon</label>
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-lg border border-border bg-surface-2">
                        @if ($branding->faviconUrl())
                            <img src="{{ $branding->faviconUrl() }}" class="max-h-12 max-w-12" alt="Current favicon">
                        @else
                            <span class="text-xs text-text-muted">None</span>
                        @endif
                    </div>
                    <input type="file" name="favicon" accept="image/*" class="input-field flex-1">
                </div>
                @if ($branding->favicon_path)
                    <form method="POST" action="{{ route('admin.settings.branding.reset-favicon') }}" class="mt-2">
                        @csrf
                        <button class="text-xs text-danger hover:underline">Reset to default</button>
                    </form>
                @endif
            </div>

            <button class="btn-brand">Save Branding</button>
        </form>
    </div>
</div>
@endsection
