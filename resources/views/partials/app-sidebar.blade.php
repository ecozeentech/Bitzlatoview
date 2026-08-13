@php $nav = \App\Support\AppFeatureMenu::groups(); @endphp
{{-- Mobile backdrop --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-30 bg-black/60 lg:hidden"></div>

<aside
    class="fixed inset-y-0 left-0 z-40 w-72 max-w-[85vw] -translate-x-full transform overflow-y-auto border-r border-border bg-surface transition-transform duration-200 ease-in-out lg:static lg:z-auto lg:w-64 lg:max-w-none lg:translate-x-0 lg:shrink-0 lg:bg-surface/60"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex h-16 items-center justify-between border-b border-border px-4 lg:hidden">
        <x-site-logo textClass="text-base font-extrabold" />
        <button type="button" @click="sidebarOpen = false" class="rounded-lg p-2 text-text-muted hover:bg-surface-2 hover:text-text-main" aria-label="Close menu">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="flex h-[calc(100vh-4rem)] flex-col overflow-y-auto px-3 py-4 lg:sticky lg:top-16">
        @foreach ($nav as $section => $items)
            <div class="mb-4">
                @if ($section)
                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-text-muted/70">{{ $section }}</p>
                @endif
                @foreach ($items as [$label, $routeName, $prefix, $translationKey])
                    <a href="{{ url($prefix) }}" @click="sidebarOpen = false" class="{{ request()->is($prefix.'*') ? 'nav-link-active' : 'nav-link' }} flex items-center gap-3">
                        <x-nav-icon :name="$label" />
                        <span>{{ \App\Support\AppFeatureMenu::translatedLabel($label, $translationKey) }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>
</aside>
