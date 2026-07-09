@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-outfit font-medium text-sm text-charcoal/80 mb-2']) }}>
    {{ $value ?? $slot }}
</label>
