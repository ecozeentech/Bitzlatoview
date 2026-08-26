@if (session('impersonator_id'))
    <div class="sticky top-0 z-50 flex flex-wrap items-center justify-between gap-2 bg-danger px-4 py-2 text-sm font-medium text-white">
        <span>👁 {{ session('impersonator_name') }} (admin) is viewing this account as {{ auth()->user()->name }}. All actions are logged.</span>
        <form method="POST" action="{{ route('stop-impersonating') }}">
            @csrf
            <button class="rounded-lg bg-white/20 px-3 py-1 text-xs font-semibold hover:bg-white/30">Exit to Admin</button>
        </form>
    </div>
@endif
