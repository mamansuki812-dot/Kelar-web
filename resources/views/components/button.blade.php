@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-primary hover:bg-primary-dark text-white shadow-sm',
        'success' => 'bg-emerald-700 hover:bg-emerald-800 text-white shadow-sm',
        'danger'  => 'bg-rose-700 hover:bg-rose-800 text-white shadow-sm',
        'neutral' => 'bg-surface hover:bg-body-bg text-neutral-dark border border-border-soft shadow-sm',
        'outline' => 'bg-rose-50 hover:bg-rose-100 text-rose-700',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];
    $baseClass = 'inline-flex items-center justify-center gap-1.5 rounded-xl font-semibold transition ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClass]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClass]) }}>{{ $slot }}</button>
@endif
