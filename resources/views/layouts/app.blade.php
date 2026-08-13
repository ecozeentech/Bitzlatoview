<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-background text-text-main" x-data="{ sidebarOpen: false }">
    @include('partials.app-topbar')
    @include('partials.pwa-install-banner')

    <div class="flex">
        @include('partials.app-sidebar')

        <main class="min-h-[calc(100vh-4rem)] w-full min-w-0 flex-1 px-4 py-6 pb-24 lg:px-8 lg:pb-6">
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

    @include('partials.app-bottom-nav')
</body>
</html>
