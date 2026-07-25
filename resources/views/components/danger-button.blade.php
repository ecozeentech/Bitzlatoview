<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-lg bg-danger px-5 py-2.5 font-semibold text-white transition hover:brightness-110']) }}>
    {{ $slot }}
</button>
