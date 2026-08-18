@props([
    'headers' => [],
])

{{-- Desktop: tabel biasa (muncul di md ke atas) --}}
<div class="hidden md:block bg-surface rounded-2xl border border-border-soft shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full divide-y divide-slate-100">
            <thead>
                <tr class="bg-body-bg">
                    @foreach($headers as $header)
                        <th class="px-3 sm:px-6 py-4 text-xs font-semibold text-muted uppercase tracking-wider {{ $header['class'] ?? 'text-left' }}">
                            {{ $header['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                {!! $desktop !!}
            </tbody>
        </table>
    </div>
</div>

{{-- Mobile: kartu vertikal (muncul di bawah md) --}}
<div class="block md:hidden space-y-3">
    {!! $mobile !!}
</div>
