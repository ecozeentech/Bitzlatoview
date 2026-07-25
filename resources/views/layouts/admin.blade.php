<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    @include('partials.head', ['title' => 'Admin'])
</head>
<body class="min-h-screen bg-background text-text-main">
    <header class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-border bg-background/90 px-4 backdrop-blur-xl lg:px-6">
        <div class="flex items-center gap-3">
            <a href="{{ url('/admin') }}" class="flex items-center gap-2 text-lg font-extrabold">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-gradient text-background">B</span>
                Bitzlato<span class="text-brand">view</span>
                <span class="pill-info ml-1">Admin</span>
            </a>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ url('/app/dashboard') }}" class="btn-ghost text-sm">Back to App</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-outline text-sm">Log Out</button>
            </form>
        </div>
    </header>

    <div class="flex">
        @include('partials.admin-sidebar')

        <main class="min-h-[calc(100vh-4rem)] flex-1 px-4 py-6 lg:px-8">
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
