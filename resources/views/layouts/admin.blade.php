<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    @include('partials.head', ['title' => 'Admin'])
</head>
<body class="min-h-screen bg-background text-text-main" x-data="{ sidebarOpen: false }">
    <header class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-border bg-background/90 px-4 backdrop-blur-xl lg:px-6">
        <div class="flex items-center gap-2 sm:gap-3">
            <button type="button" @click="sidebarOpen = !sidebarOpen" class="rounded-lg p-2 text-text-muted hover:bg-surface-2 hover:text-text-main lg:hidden" aria-label="Toggle menu">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ url('/admin') }}" class="flex items-center gap-2">
                <x-site-logo textClass="hidden text-lg font-extrabold sm:inline" />
                <span class="pill-info ml-1">Admin</span>
            </a>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ url('/app/dashboard') }}" class="btn-ghost hidden text-sm sm:inline-flex">Back to App</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-outline text-sm">Log Out</button>
            </form>
        </div>
    </header>

    <div class="flex">
        @include('partials.admin-sidebar')

        <main class="min-h-[calc(100vh-4rem)] w-full min-w-0 flex-1 px-4 py-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-sm text-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-danger">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
