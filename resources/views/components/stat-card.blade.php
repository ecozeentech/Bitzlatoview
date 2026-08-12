@props(['icon' => 'dashboard', 'label', 'value', 'sub' => null, 'href' => null, 'tone' => 'brand'])
@php
    $toneClasses = [
        'brand' => 'bg-brand/10 text-brand',
        'success' => 'bg-success/10 text-success',
        'danger' => 'bg-danger/10 text-danger',
        'info' => 'bg-info/10 text-info',
        'muted' => 'bg-surface-2 text-text-muted',
    ][$tone] ?? 'bg-brand/10 text-brand';
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif class="glass-card group flex items-start gap-4 p-5 transition hover:border-brand/30">
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $toneClasses }}">
        <x-nav-icon :name="$icon" class="h-5 w-5" />
    </span>
    <div class="min-w-0 flex-1">
        <p class="text-xs uppercase tracking-wide text-text-muted">{{ $label }}</p>
        <p class="mt-1 truncate font-numeric text-xl font-bold text-text-main">{{ $value }}</p>
        @if ($sub)
            <p class="mt-1 text-xs text-text-muted">{{ $sub }}</p>
        @endif
    </div>
    @if ($href)
        <svg class="mt-1 h-4 w-4 shrink-0 text-text-muted transition group-hover:translate-x-0.5 group-hover:text-brand" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L15.586 11H3a1 1 0 110-2h12.586l-3.293-3.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    @endif
</{{ $tag }}>
