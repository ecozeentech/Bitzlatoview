@php $branding = \App\Models\BrandingSetting::current(); @endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? $branding->site_name }} — {{ $branding->site_name }}</title>
<meta name="description" content="{{ $description ?? $branding->site_name.' — trade crypto, stocks, forex, futures, NFTs and more from one modern exchange dashboard.' }}">
@if ($branding->faviconUrl())
    <link rel="icon" href="{{ $branding->faviconUrl() }}">
@else
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2220%22 fill=%22%23070A12%22/><text x=%2250%25%22 y=%2258%25%22 font-size=%2250%22 text-anchor=%22middle%22 fill=%22%23F5B301%22 font-family=%22Arial%22 font-weight=%22bold%22>{{ substr($branding->site_name, 0, 1) }}</text></svg>">
@endif
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|jetbrains-mono:400,500,600" rel="stylesheet" />
@vite(['resources/css/app.css', 'resources/js/app.js'])
