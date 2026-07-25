@props(['value'])

<label {{ $attributes->merge(['class' => 'label-field']) }}>
    {{ $value ?? $slot }}
</label>
