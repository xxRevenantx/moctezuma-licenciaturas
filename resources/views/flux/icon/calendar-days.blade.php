{{-- Credit: Lucide (https://lucide.dev) --}}

@props([
    'variant' => 'outline',
])

@php
if ($variant === 'solid') {
    throw new \Exception('The "solid" variant is not supported in Lucide.');
}

$classes = Flux::classes('shrink-0')
    ->add(match($variant) {
        'outline' => '[:where(&)]:size-6',
        'solid' => '[:where(&)]:size-6',
        'mini' => '[:where(&)]:size-5',
        'micro' => '[:where(&)]:size-4',
    });

$strokeWidth = match ($variant) {
    'outline' => 2,
    'mini' => 2.25,
    'micro' => 2.5,
};
@endphp

<svg
    {{ $attributes->class($classes) }}
    data-flux-icon
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    data-slot="icon"
>
  <path d="M8 2v4" />
  <path d="M16 2v4" />
  <rect width="18" height="18" x="3" y="4" rx="2" />
  <path d="M3 10h18" />
  <path d="M8 14h.01" />
  <path d="M12 14h.01" />
  <path d="M16 14h.01" />
  <path d="M8 18h.01" />
  <path d="M12 18h.01" />
  <path d="M16 18h.01" />
</svg>
