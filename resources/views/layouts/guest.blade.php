<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="flex min-h-screen items-center justify-center bg-background px-4 py-10 text-text-main">
    <div class="fixed right-4 top-4">
        <x-language-switcher />
    </div>
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                <x-site-logo textClass="text-2xl font-extrabold" />
            </a>
        </div>

        <div class="glass-card p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-center text-xs text-text-muted">
            By continuing you agree to our <a href="/terms" class="text-brand hover:underline">Terms</a>, <a href="/privacy" class="text-brand hover:underline">Privacy Policy</a> and <a href="/risk-disclosure" class="text-brand hover:underline">Risk Disclosure</a>.
        </p>
    </div>
</body>
</html>
