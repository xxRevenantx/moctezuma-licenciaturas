@props(['status' => 'PENDING'])

@php
    $s = strtoupper(trim($status));
    $styles = [
        'PENDING'  => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 ring-indigo-500/20',
        'APPROVED' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 ring-emerald-500/20',
        'REJECT'   => 'bg-rose-500/10 text-rose-600 dark:text-rose-300 ring-rose-500/20',
    ];
    $class = $styles[$s] ?? $styles['PENDING'];
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold tracking-wide uppercase ring-1 {{ $class }}">
    {{ $s }}
</span>
