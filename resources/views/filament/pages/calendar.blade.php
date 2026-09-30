<x-filament-panels::page>
    @php
        $colors = ['new' => '#f59e0b', 'confirmed' => '#0ea5e9', 'preparing' => '#8a1c2b', 'delivered' => '#10b981', 'returned' => '#a8a29e'];
        $weekdays = ['B.e', 'Ç.a', 'Ç', 'C.a', 'C', 'Ş', 'B'];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <x-filament::icon-button icon="heroicon-m-chevron-left" wire:click="shift(-1)" label="Əvvəlki ay" />
            <h2 class="min-w-44 text-center text-xl font-bold capitalize">{{ $first->translatedFormat('F Y') }}</h2>
            <x-filament::icon-button icon="heroicon-m-chevron-right" wire:click="shift(1)" label="Növbəti ay" />
            <x-filament::button size="sm" color="gray" wire:click="today">Bu ay</x-filament::button>
        </div>
        <x-filament::tabs>
            <x-filament::tabs.item :active="$view_mode === 'events'" wire:click="$set('view_mode', 'events')" icon="heroicon-m-calendar">Məclislər</x-filament::tabs.item>
            <x-filament::tabs.item :active="$view_mode === 'stock'" wire:click="$set('view_mode', 'stock')" icon="heroicon-m-table-cells">Xonça məşğulluğu</x-filament::tabs.item>
        </x-filament::tabs>
    </div>

    @if ($view_mode === 'events')
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 text-center text-xs font-semibold text-gray-500 dark:border-white/10 dark:bg-white/5">
                @foreach ($weekdays as $w)
                    <div class="py-2">{{ $w }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7">
                @foreach ($days as $day)
                    <div @class([
                        'min-h-28 border-b border-r border-gray-100 p-1.5 dark:border-white/5',
                        'bg-gray-50/60 dark:bg-white/[.02]' => ! $day['in'],
                    ])>
                        <div @class([
                            'mb-1 grid size-7 place-items-center rounded-full text-xs font-bold',
                            'bg-primary-600 text-white' => $day['today'],
                            'text-gray-400' => ! $day['in'] && ! $day['today'],
                        ])>{{ $day['date']->day }}</div>
                        <div class="space-y-1">
                            @foreach ($day['bookings'] as $b)
                                <a href="{{ $url($b) }}" class="block truncate rounded-md px-1.5 py-1 text-[11px] leading-tight font-semibold text-white hover:opacity-90"
                                   style="background: {{ $colors[$b->status] ?? '#78716c' }}" title="{{ $b->customer_name }} · {{ $b->items->pluck('xonca_name')->implode(', ') }}">
                                    {{ $b->event_time ? $b->event_time . ' ' : '' }}{{ $b->customer_name }}
                                    <span class="opacity-80">· {{ $b->items->count() }} xonça</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="flex flex-wrap gap-4 text-xs text-gray-500">
            @foreach (\App\Models\Booking::STATUSES as $k => $label)
                @if (isset($colors[$k]))
                    <span class="flex items-center gap-1.5"><span class="size-3 rounded" style="background: {{ $colors[$k] }}"></span>{{ $label }}</span>
                @endif
            @endforeach
        </div>
    @else
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5">
                        <th class="sticky left-0 z-10 bg-gray-50 px-3 py-2 text-left font-semibold dark:bg-gray-900">Xonça (anbar)</th>
                        @foreach ($monthDays as $d)
                            <th @class(['px-1 py-2 text-center font-semibold', 'text-primary-600' => $d->isToday(), 'text-gray-400' => $d->isWeekend()])>{{ $d->day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($matrix as $m)
                        <tr class="border-t border-gray-100 dark:border-white/5">
                            <td class="sticky left-0 z-10 bg-white px-3 py-1.5 font-semibold whitespace-nowrap dark:bg-gray-900">{{ $m['xonca']->name }} <span class="text-gray-400">({{ $m['xonca']->stock }})</span></td>
                            @foreach ($m['row'] as $n)
                                @php($full = $n >= $m['xonca']->stock)
                                <td class="p-0.5 text-center">
                                    <span @class([
                                        'grid h-7 min-w-7 place-items-center rounded font-bold',
                                        'bg-rose-500 text-white' => $full && $n > 0,
                                        'bg-amber-200 text-amber-900' => ! $full && $n > 0,
                                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10' => $n === 0,
                                    ])>{{ $n ?: '' }}</span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500">Rəqəm — həmin gün neçə ədədin çöldə olduğu (aparma və qaytarma günləri daxil). <span class="font-semibold text-rose-600">Qırmızı</span> — hamısı tutulub.</p>
    @endif
</x-filament-panels::page>
