@php
    $languages = [
        'en' => ['English', '🇬🇧'],
        'es' => ['Español', '🇪🇸'],
        'fr' => ['Français', '🇫🇷'],
        'ar' => ['العربية', '🇸🇦'],
        'zh' => ['中文', '🇨🇳'],
    ];
    $current = $languages[app()->getLocale()] ?? $languages['en'];
@endphp
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center gap-1.5 rounded-lg border border-border bg-surface-2 px-2.5 py-1.5 text-sm text-text-muted hover:text-text-main" aria-label="Change language">
        <span>{{ $current[1] }}</span>
        <span class="hidden sm:inline">{{ strtoupper(app()->getLocale()) }}</span>
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.39a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
    </button>
    <div x-show="open" @click.outside="open = false" x-transition x-cloak class="absolute right-0 mt-2 w-40 rounded-xl border border-border bg-surface p-1.5 shadow-glass">
        @foreach ($languages as $code => [$label, $flag])
            <form method="POST" action="{{ route('locale.update', $code) }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm {{ app()->getLocale() === $code ? 'bg-surface-2 text-brand' : 'text-text-muted hover:bg-surface-2 hover:text-text-main' }}">
                    <span>{{ $flag }}</span> {{ $label }}
                </button>
            </form>
        @endforeach
    </div>
</div>
