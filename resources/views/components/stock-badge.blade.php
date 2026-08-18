@props(['status' => 'aman', 'dot' => false])

@php
    $classes = [
        'aman'    => 'bg-emerald-100 text-emerald-700',
        'menipis' => 'bg-amber-100 text-amber-700',
        'habis'   => 'bg-rose-100 text-rose-700',
    ];
    $dots = [
        'aman'    => 'bg-emerald-500',
        'menipis' => 'bg-amber-500',
        'habis'   => 'bg-rose-500',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full ' . ($classes[$status] ?? $classes['aman'])]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full inline-block {{ $dots[$status] ?? $dots['aman'] }}"></span>
    @endif
    {{ $slot }}
</span>
