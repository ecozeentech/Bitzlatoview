<header class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-border bg-background/90 px-4 backdrop-blur-xl lg:px-6">
    <div class="flex items-center gap-2 sm:gap-4">
        <button type="button" @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-text-muted hover:bg-surface-2 hover:text-text-main lg:hidden" aria-label="Toggle menu">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            <x-site-logo textClass="hidden text-lg font-extrabold sm:inline" />
        </a>
        <div class="hidden items-center rounded-lg border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-muted md:flex">
            <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
            <input type="text" placeholder="Search markets, orders, help..." class="w-48 bg-transparent outline-none placeholder:text-text-muted">
        </div>
    </div>

    <div class="flex items-center gap-3">
        @if (auth()->user()->kyc_status !== 'approved')
            <a href="{{ url('/kyc-onboarding') }}" class="pill-warning hidden sm:inline-flex">KYC: {{ str_replace('_',' ', auth()->user()->kyc_status) }}</a>
        @endif
        <a href="{{ url('/app/funding/deposit') }}" class="btn-brand hidden text-sm sm:inline-flex">Deposit</a>
        <a href="{{ url('/app/settings/wallet-connect') }}" class="btn-outline hidden text-sm md:inline-flex">Connect Wallet</a>

        <div class="relative" x-data="{ open: false }" x-init="$watch('open', (value) => { if (value) { fetch('{{ route('app.notifications.mark-read') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } }); } })">
            <button @click="open = !open" class="relative rounded-lg border border-border bg-surface-2 p-2 text-text-muted hover:text-text-main" aria-label="Notifications">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.85 23.85 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                @if (($unreadNotifications ?? 0) > 0)
                    <span class="absolute -right-1 -top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-danger px-1 text-[10px] font-bold text-white">{{ min($unreadNotifications, 9) }}{{ $unreadNotifications > 9 ? '+' : '' }}</span>
                @endif
            </button>
            <div x-show="open" @click.outside="open = false" x-transition x-cloak class="absolute right-0 mt-2 w-80 max-w-[90vw] rounded-xl border border-border bg-surface p-2 shadow-glass">
                <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-text-muted">Recent Activity</p>
                <div class="max-h-80 overflow-y-auto">
                    @forelse (($notifications ?? []) as $n)
                        <a href="{{ url($n['url']) }}" class="flex items-start gap-2 rounded-lg px-3 py-2 text-sm hover:bg-surface-2">
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $n['icon'] === 'success' ? 'bg-success' : ($n['icon'] === 'danger' ? 'bg-danger' : 'bg-info') }}"></span>
                            <span>
                                <span class="block text-text-main">{{ $n['text'] }}</span>
                                <span class="text-xs text-text-muted">{{ $n['at']->diffForHumans() }}</span>
                            </span>
                        </a>
                    @empty
                        <p class="px-3 py-4 text-center text-sm text-text-muted">No recent activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-2 rounded-lg border border-border bg-surface-2 px-3 py-1.5 text-sm">
                <span class="h-6 w-6 rounded-full bg-brand-gradient text-center text-xs font-bold leading-6 text-background">{{ substr(auth()->user()->name,0,1) }}</span>
                <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
            </button>
            <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-52 rounded-xl border border-border bg-surface p-2 shadow-glass">
                <a href="{{ url('/app/settings/profile') }}" class="block rounded-lg px-3 py-2 text-sm text-text-muted hover:bg-surface-2 hover:text-text-main">Profile Settings</a>
                <a href="{{ url('/app/settings/security') }}" class="block rounded-lg px-3 py-2 text-sm text-text-muted hover:bg-surface-2 hover:text-text-main">Security</a>
                <a href="{{ url('/app/settings/kyc') }}" class="block rounded-lg px-3 py-2 text-sm text-text-muted hover:bg-surface-2 hover:text-text-main">KYC Verification</a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ url('/admin') }}" class="block rounded-lg px-3 py-2 text-sm text-brand hover:bg-surface-2">Admin Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-danger hover:bg-surface-2">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</header>
