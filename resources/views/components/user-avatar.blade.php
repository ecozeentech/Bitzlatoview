@props(['user', 'size' => 'h-6 w-6 text-xs'])

@if ($user->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" {{ $attributes->merge(['class' => $size.' shrink-0 rounded-full object-cover']) }}>
@else
    <span {{ $attributes->merge(['class' => $size.' shrink-0 rounded-full bg-brand-gradient text-center font-bold leading-none text-background flex items-center justify-center']) }}>{{ substr($user->name, 0, 1) }}</span>
@endif
