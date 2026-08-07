@props(['textClass' => 'text-xl font-extrabold tracking-tight'])
@php $branding = \App\Models\BrandingSetting::current(); @endphp

@if ($branding->logoUrl())
    <img src="{{ $branding->logoUrl() }}" alt="{{ $branding->site_name }}" class="h-8 w-auto">
@else
    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-gradient text-background font-extrabold">{{ substr($branding->site_name, 0, 1) }}</span>
    @if ($branding->site_name === 'Bitzlatoview')
        <span class="{{ $textClass }}">Bitzlato<span class="text-brand">view</span></span>
    @else
        <span class="{{ $textClass }}">{{ $branding->site_name }}</span>
    @endif
@endif
