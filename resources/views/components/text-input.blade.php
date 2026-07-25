@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-xl border border-border bg-surface-2 px-4 py-2.5 text-sm text-slate-100 placeholder:text-muted/70 focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand']) }}>
