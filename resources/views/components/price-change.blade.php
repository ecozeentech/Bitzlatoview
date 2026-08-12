@props(['value' => 0])
@php $v = (float) $value; @endphp
<span {{ $attributes->merge(['class' => 'font-numeric text-sm font-semibold '.($v >= 0 ? 'price-up' : 'price-down')]) }}>
    {{ $v >= 0 ? '+' : '' }}{{ number_format($v, 2) }}%
</span>
