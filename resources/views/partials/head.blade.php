@php $branding = \App\Models\BrandingSetting::current(); @endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? $branding->site_name }} — {{ $branding->site_name }}</title>
<meta name="description" content="{{ $description ?? $branding->site_name.' — trade crypto, stocks, forex, futures, NFTs and more from one modern exchange dashboard.' }}">
@if ($branding->faviconUrl())
    <link rel="icon" href="{{ $branding->faviconUrl() }}">
    <link rel="apple-touch-icon" href="{{ $branding->faviconUrl() }}">
@else
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2220%22 fill=%22%23070A12%22/><text x=%2250%25%22 y=%2258%25%22 font-size=%2250%22 text-anchor=%22middle%22 fill=%22%23F5B301%22 font-family=%22Arial%22 font-weight=%22bold%22>{{ substr($branding->site_name, 0, 1) }}</text></svg>">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('icons/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/icon-180x180.png') }}">
@endif

{{-- Progressive Web App --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#0B0F1A">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ $branding->site_name }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|jetbrains-mono:400,500,600" rel="stylesheet" />
@vite(['resources/css/app.css', 'resources/js/app.js'])
