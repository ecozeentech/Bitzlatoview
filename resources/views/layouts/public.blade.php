<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
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
