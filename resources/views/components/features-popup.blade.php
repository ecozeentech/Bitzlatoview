{{-- Triggered by setting the Alpine `featuresOpen` boolean to true (see the "More" button in
     the dashboard feature bar). Pulls from App\Support\AppFeatureMenu — the same source of
     truth as the sidebar — so it can never miss a feature the sidebar already has. --}}
<div
    x-show="featuresOpen"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-end justify-center bg-black/70 p-0 sm:items-center sm:p-4"
    @keydown.escape.window="featuresOpen = false"
>
    <div
        x-show="featuresOpen"
        x-transition
        @click.outside="featuresOpen = false"
        class="max-h-[85vh] w-full max-w-2xl overflow-y-auto rounded-t-2xl border border-border bg-surface p-6 shadow-glass sm:rounded-2xl"
    >
        <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-border sm:hidden"></div>
        <div class="mb-4 flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wide text-text-muted">{{ __('common.all_features') }}</p>
            <button type="button" @click="featuresOpen = false" class="flex h-8 w-8 items-center justify-center rounded-full bg-surface-2 text-text-muted hover:text-text-main" aria-label="Close">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        @foreach (\App\Support\AppFeatureMenu::groups() as $group => $items)
            @if ($group)
                <p class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-text-muted/70 first:mt-0">{{ $group }}</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ($items as [$label, $routeName, $prefix, $translationKey])
                        <a href="{{ url($prefix) }}" @click="featuresOpen = false" class="flex flex-col items-center gap-2 rounded-xl border border-border bg-surface-2/40 p-3 text-center text-xs transition hover:border-brand/40 hover:bg-surface-2">
                            <x-nav-icon :name="$label" class="h-5 w-5 text-brand" />
                            <span class="text-text-main">{{ \App\Support\AppFeatureMenu::translatedLabel($label, $translationKey) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
</div>
