@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold">API Keys</h1>
    <div class="risk-banner">Public API access for programmatic trading is not yet available — keys generated here are not currently wired to any trading endpoints.</div>

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('app.settings.api-keys.generate') }}" class="flex gap-2">
            @csrf
            <input type="text" name="label" class="input-field" placeholder="Key label (optional)">
            <button class="btn-brand text-sm">Generate Key</button>
        </form>
    </div>

    <div class="glass-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Label</th><th>Key</th><th>Created</th><th></th></tr></thead>
            <tbody>
                @forelse ($keys as $key)
                    <tr>
                        <td>{{ $key->label }}</td>
                        <td class="font-numeric">{{ $key->key }}</td>
                        <td class="text-text-muted">{{ $key->created_at->format('M d, Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('app.settings.api-keys.revoke') }}">
                                @csrf @method('DELETE')
                                <input type="hidden" name="id" value="{{ $key->id }}">
                                <button class="text-xs text-danger hover:underline">Revoke</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-text-muted">No API keys yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
