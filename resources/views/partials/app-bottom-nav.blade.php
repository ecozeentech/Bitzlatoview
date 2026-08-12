@php
    $bottomNav = [
        ['Home', 'dashboard', 'app/dashboard'],
        ['Markets', 'chart-bar', 'app/markets'],
        ['Trade', 'presentation', 'app/spot'],
        ['Wallet', 'wallet', 'app/wallet/primary'],
    ];
@endphp
<nav class="fixed inset-x-0 bottom-0 z-30 flex border-t border-border bg-surface/95 backdrop-blur-xl lg:hidden" style="padding-bottom: env(safe-area-inset-bottom)">
    @foreach ($bottomNav as [$label, $icon, $prefix])
        <a href="{{ url($prefix) }}" class="flex flex-1 flex-col items-center gap-1 py-2.5 text-[11px] {{ request()->is($prefix.'*') ? 'text-brand' : 'text-text-muted' }}">
            <x-nav-icon :name="$icon" class="h-5 w-5" />
            {{ $label }}
        </a>
    @endforeach
    <button type="button" @click="sidebarOpen = true" class="flex flex-1 flex-col items-center gap-1 py-2.5 text-[11px] text-text-muted">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" /></svg>
        Menu
    </button>
</nav>
