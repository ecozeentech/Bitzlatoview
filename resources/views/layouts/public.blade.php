<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="dark scroll-smooth">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-background text-text-main">
    @include('partials.public-header')

    <main>
        @yield('content')
    </main>

    @include('partials.public-footer')
</body>
</html>
