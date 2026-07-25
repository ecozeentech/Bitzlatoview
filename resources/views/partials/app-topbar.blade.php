<header class="sticky top-0 z-30 flex items-center justify-between gap-4 border-b border-border bg-surface/90 px-4 py-3 backdrop-blur md:px-6">
    <div class="min-w-0">
        <p class="truncate text-sm text-muted">Welcome back, {{ auth()->user()->name }}</p>
        <p class="text-xs text-brand">Simulation / paper-trading mode</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('app.wallet.deposit') }}" class="btn-brand text-xs">Deposit</a>
        <a href="{{ route('app.settings.wallet-connect') }}" class="btn-outline text-xs">WalletConnect</a>
        <a href="{{ route('app.settings.profile') }}" class="btn-ghost text-xs">{{ auth()->user()->email }}</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-ghost text-xs">Logout</button></form>
    </div>
</header>
