@extends('layouts.public')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-16 lg:px-8">
    <h1 class="text-3xl font-bold">Cookie Policy</h1>
    <p class="mt-2 text-sm text-text-muted">Last updated: {{ now()->format('F j, Y') }}</p>

    <div class="prose prose-invert mt-8 max-w-none space-y-5 text-text-muted">
        <p>Bitzlatoview uses cookies and similar technologies to keep you signed in, remember your preferences (such as theme), and understand how the Platform is used.</p>

        <h2 class="text-text-main">Essential Cookies</h2>
        <p>Required for core functionality such as authentication sessions and CSRF protection. These cannot be disabled without breaking the Platform.</p>

        <h2 class="text-text-main">Preference Cookies</h2>
        <p>Remember settings like theme (dark/light) and dashboard layout choices.</p>

        <h2 class="text-text-main">Analytics Cookies</h2>
        <p>Help us understand aggregate usage patterns to improve the Platform. You can opt out of non-essential cookies via your browser settings.</p>
    </div>
</div>
@endsection
