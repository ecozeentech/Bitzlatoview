<aside class="hidden w-64 shrink-0 border-r border-border bg-surface lg:block">
    <div class="sticky top-0 h-screen overflow-y-auto p-4">
        <a href="{{ route('admin.dashboard') }}" class="mb-6 flex items-center gap-2 px-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand text-xs font-extrabold text-background">BZ</span>
            <div>
                <p class="font-bold leading-none">Bitzlatoview</p>
                <p class="text-[10px] uppercase tracking-wider text-muted">Admin</p>
            </div>
        </a>
        <nav class="space-y-1">
            @foreach([
                ['Dashboard','admin.dashboard'],
                ['Users','admin.users'],
                ['KYC','admin.kyc'],
                ['Deposits','admin.deposits'],
                ['Withdrawals','admin.withdrawals'],
                ['Ledger','admin.ledger'],
                ['Adjustments','admin.adjustments'],
                ['Orders','admin.orders'],
                ['Markets','admin.markets'],
                ['P2P','admin.p2p'],
                ['Email','admin.module','email'],
                ['News','admin.module','news'],
                ['Blog','admin.module','blog'],
                ['CMS','admin.module','cms'],
                ['Copy Trading','admin.module','copy-trading'],
                ['AI Bots','admin.module','ai-bots'],
                ['Mining','admin.module','mining'],
                ['Cards','admin.module','virtual-cards'],
                ['Tax','admin.module','tax'],
                ['Risk','admin.module','risk'],
                ['Audit Logs','admin.module','audit-logs'],
                ['Settings','admin.module','settings'],
            ] as $item)
                @if(($item[1] ?? '') === 'admin.module')
                    <a href="{{ route('admin.module', $item[2]) }}" class="sidebar-link">{{ $item[0] }}</a>
                @else
                    <a href="{{ route($item[1]) }}" class="sidebar-link">{{ $item[0] }}</a>
                @endif
            @endforeach
        </nav>
    </div>
</aside>
