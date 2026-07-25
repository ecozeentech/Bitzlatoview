<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Bitzlatoview') }}</title>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-50 antialiased">
        <div class="flex min-h-screen flex-col items-center bg-background bg-hero-grid pt-6 sm:justify-center sm:pt-0">
            <div class="mt-6 w-full overflow-hidden px-6 py-6 sm:max-w-md sm:rounded-2xl sm:border sm:border-border sm:bg-surface/90 sm:shadow-glass sm:backdrop-blur">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
