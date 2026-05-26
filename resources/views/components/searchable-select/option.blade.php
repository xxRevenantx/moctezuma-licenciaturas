@props([
    'value' => null,
])

@php
    $label = trim(preg_replace('/\s+/', ' ', strip_tags((string) $slot)));
@endphp

<span data-option data-value="{{ $value }}" data-label="{{ $label }}">
    {{ $slot }}
</span>
