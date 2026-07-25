<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-background transition hover:bg-brand-soft focus:outline-none focus:ring-2 focus:ring-brand/50']) }}>
    {{ $slot }}
</button>
