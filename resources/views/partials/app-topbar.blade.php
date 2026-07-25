<header class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-border bg-background/90 px-4 backdrop-blur-xl lg:px-6">
    <div class="flex items-center gap-4">
        <a href="{{ url('/') }}" class="flex items-center gap-2 text-lg font-extrabold">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-gradient text-background">B</span>
            <span class="hidden sm:inline">Bitzlato<span class="text-brand">view</span></span>
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
