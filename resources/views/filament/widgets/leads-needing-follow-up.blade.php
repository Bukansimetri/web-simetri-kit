<x-filament-widgets::widget>
    <x-filament::section heading="Perlu ditindaklanjuti">
        @php
            $queue = $this->getQueue();
        @endphp

        @if (empty($queue))
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Semua prospek sudah ditindaklanjuti
            </p>
        @else
            <div class="fi-ta-content overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">Nama</th>
                            <th class="px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">Sumber</th>
                            <th class="px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">Area</th>
                            <th class="px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">Masuk</th>
                            <th class="px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">Estimasi Tagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach ($queue as $item)
                            <tr
                                tabindex="0"
                                class="cursor-pointer transition hover:bg-gray-50 dark:hover:bg-white/5"
                                onclick="window.location='{{ $item['url'] }}'"
                            >
                                <td class="px-3 py-4 text-sm text-gray-950 dark:text-white">
                                    {{ $item['name'] }}
                                </td>
                                <td class="px-3 py-4 text-sm">
                                    <x-filament::badge :color="$item['source'] === 'calculator' ? 'info' : 'warning'">
                                        {{ $item['sourceLabel'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $item['area'] ?: '-' }}
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $item['ageLabel'] }}

                                    @if ($item['isOverdue'])
                                        <x-filament::badge color="danger">
                                            Terlambat
                                        </x-filament::badge>
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $item['estimatedMonthlyBill'] !== null ? 'Rp '.number_format($item['estimatedMonthlyBill'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2">
            <x-filament::link :href="$this->getContactListUrl()">
                Lihat semua pesan masuk baru
            </x-filament::link>

            <x-filament::link :href="$this->getCalculatorListUrl()">
                Lihat semua lead kalkulator baru
            </x-filament::link>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
