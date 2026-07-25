@props(['symbol' => '?'])
<span class="flex h-8 w-8 items-center justify-center rounded-full bg-surface-2 text-xs font-bold text-brand ring-1 ring-border">
    {{ strtoupper(substr($symbol, 0, 1)) }}
</span>
