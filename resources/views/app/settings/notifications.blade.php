@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <h1 class="text-2xl font-bold">Notification Preferences</h1>
    <div class="glass-card p-6">
        <form method="POST" action="{{ route('app.settings.notifications.update') }}" class="space-y-3">
            @csrf
            @foreach (['Login alerts', 'Deposit/withdrawal updates', 'P2P order updates', 'Price alerts', 'Marketing emails'] as $pref)
                <label class="flex items-center justify-between rounded-lg border border-border px-4 py-3 text-sm">
                    {{ $pref }}
                    <input type="checkbox" name="prefs[]" value="{{ \Illuminate\Support\Str::slug($pref) }}" checked class="rounded border-border bg-surface-2">
                </label>
            @endforeach
            <button class="btn-brand w-full">Save Preferences</button>
        </form>
    </div>
</div>
@endsection
