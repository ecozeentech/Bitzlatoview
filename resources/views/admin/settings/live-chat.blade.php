@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <h1 class="text-2xl font-bold">Live Support (Tawk.to)</h1>
    <p class="text-sm text-text-muted">Add your Tawk.to Property ID and Widget ID to show the live chat widget to logged-in users and site visitors. Find these in your Tawk.to dashboard under Administration → Channels → Chat Widget → "Direct Chat Link" / embed code (the two IDs in the URL <code>https://embed.tawk.to/&lt;property_id&gt;/&lt;widget_id&gt;</code>).</p>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('admin.settings.live-chat.update') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label-field">Property ID</label>
                <input type="text" name="property_id" class="input-field" value="{{ $setting->property_id }}" placeholder="e.g. 5f1a2b3c4d5e6f7g8h9i0j1k">
            </div>
            <div>
                <label class="label-field">Widget ID</label>
                <input type="text" name="widget_id" class="input-field" value="{{ $setting->widget_id }}" placeholder="e.g. default">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_enabled" value="1" @checked($setting->is_enabled) class="rounded border-border bg-surface-2 text-brand focus:ring-brand">
                Enable live chat widget site-wide
            </label>
            @if ($setting->isActive())
                <p class="text-xs text-success">Widget is active and will appear on the public site and user dashboard.</p>
            @else
                <p class="text-xs text-text-muted">Widget is not active — enable it and provide both IDs above.</p>
            @endif
            <button class="btn-brand text-sm">Save Settings</button>
        </form>
    </div>
</div>
@endsection
