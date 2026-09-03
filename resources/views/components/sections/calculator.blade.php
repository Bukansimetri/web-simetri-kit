<section id="kalkulator" class="relative z-20 max-w-6xl mx-auto px-6 -mt-32 mb-32">
    <div
        x-data="calculatorComponent()"
        class="bg-white p-8 md:p-12 shadow-2xl border border-gray-50/50 max-w-5xl mx-auto rounded-lg"
    >
        <div class="text-center mb-10">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-2">Hitung Estimasi Penghematan</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Dapatkan analisis mendalam untuk kebutuhan energi Anda.</p>
        </div>

        <div class="flex flex-col gap-8">
            {{-- Mode Tabs --}}
            <div class="flex justify-center p-1 bg-surface-container-low rounded-lg max-w-md mx-auto mb-4 border border-outline-variant/30">
                <button
                    type="button"
                    @click="mode = 'bill'; result = null; error = null"
                    :class="mode === 'bill' ? 'bg-primary text-white shadow-sm' : 'text-primary hover:bg-white/50'"
                    class="flex-1 py-3 px-6 rounded-md font-bold text-sm transition-all active:scale-95"
                >
                    Per Tagihan
                </button>
                <button
                    type="button"
                    @click="mode = 'appliance'; result = null; error = null"
                    :class="mode === 'appliance' ? 'bg-primary text-white shadow-sm' : 'text-primary hover:bg-white/50'"
                    class="flex-1 py-3 px-6 rounded-md font-bold text-sm transition-all active:scale-95"
                >
                    Per Alat
                </button>
            </div>

            <div class="flex flex-col md:flex-row items-stretch gap-12">
                {{-- Input Side --}}
                <div class="md:w-1/2 flex flex-col justify-between">
                    {{-- Mode: Per Tagihan --}}
                    <div x-show="mode === 'bill'" class="space-y-4">
                        <label class="block text-sm font-bold text-primary/70">Tagihan Listrik Bulanan (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">Rp</span>
                            <input
                                type="text"
                                x-model="billInput"
                                placeholder="2.500.000"
                                class="w-full pl-12 pr-4 py-4 bg-surface-container-low border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-lg font-semibold rounded-lg"
                            >
                        </div>
                    </div>

                    {{-- Mode: Per Alat --}}
                    <div x-show="mode === 'appliance'" class="space-y-4">
                        <p class="text-sm text-primary/70 mb-2">Pilih jumlah peralatan listrik di rumah Anda:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-64 overflow-y-auto pr-2">
                            <template x-for="item in appliances" :key="item.key">
                                <div class="flex items-center justify-between bg-surface-container-low p-3 border border-transparent rounded-lg h-20">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-primary shadow-sm">
                                            <span class="material-symbols-outlined" x-text="item.icon"></span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-sm text-on-surface" x-text="item.label"></span>
                                            <span class="text-[10px] text-primary/60" x-text="'~' + item.watt + 'W'"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center bg-white rounded-lg border border-outline-variant overflow-hidden h-10">
                                        <button type="button" @click="item.qty = Math.max(0, item.qty - 1)" class="w-7 h-7 flex items-center justify-center text-primary hover:bg-primary/5">-</button>
                                        <input type="number" min="0" x-model.number="item.qty" class="w-8 h-7 text-center bg-transparent border-none p-0 text-xs font-bold focus:ring-0">
                                        <button type="button" @click="item.qty++" class="w-7 h-7 flex items-center justify-center text-primary hover:bg-primary/5">+</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <p x-show="error" x-cloak x-text="error" class="text-sm text-error font-medium mt-4"></p>

                    <button
                        type="button"
                        @click="calculate()"
                        id="btn-calculate"
                        class="btn-fill text-white py-5 font-bold hover:shadow-lg active:scale-[0.98] transition-all mt-6 flex items-center justify-center gap-2 bg-primary-container w-full rounded-lg"
                    >
                        Dapatkan Hasil Analisis
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                </div>

                {{-- Output Side --}}
                <div class="md:w-1/2 bg-surface-container-low flex flex-col justify-center border border-primary/10 rounded-lg">
                    <div class="w-full h-full p-6 flex flex-col gap-6">
                        <div class="flex justify-between items-center">
                            <h3 class="font-headline-lg text-sm text-primary">Cumulative Savings vs. Investment</h3>
                            <div class="flex gap-3">
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-primary"></div>
                                    <span class="text-[10px] font-medium">Savings</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-outline-variant"></div>
                                    <span class="text-[10px] font-medium">Investment</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 relative min-h-[180px]">
                            <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 400 150">
                                <line stroke="#bec7d3" stroke-dasharray="4" stroke-width="0.5" x1="0" x2="400" y1="120" y2="120"></line>
                                <line stroke="#bec7d3" stroke-dasharray="4" stroke-width="0.5" x1="0" x2="400" y1="80" y2="80"></line>
                                <line stroke="#bec7d3" stroke-dasharray="4" stroke-width="0.5" x1="0" x2="400" y1="40" y2="40"></line>
                                <path d="M 0 100 L 400 100" fill="none" stroke="#bec7d3" stroke-width="2"></path>
                                <path :d="chartPath" fill="none" stroke="#0099e5" stroke-width="3"></path>
                                <path :d="chartFillPath" fill="url(#calc-grad)" opacity="0.1"></path>
                                <defs>
                                    <linearGradient id="calc-grad" x1="0%" x2="0%" y1="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#0099e5;stop-opacity:1"></stop>
                                        <stop offset="100%" style="stop-color:#0099e5;stop-opacity:0"></stop>
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="absolute bottom-0 left-0 w-full flex justify-between text-[10px] text-outline font-bold pt-2">
                                <span>Year 0</span><span>Year 5</span><span>Year 10</span><span>Year 15</span><span>Year 20</span><span>Year 25</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3" data-testid="calculator-result">
                            <div class="bg-white p-3 rounded-lg border border-outline-variant/20 shadow-sm">
                                <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Est. Savings (Tahun 1)</p>
                                <p class="font-headline-lg text-lg text-primary" x-text="result ? formatRupiah(result.savingsYear1) : '—'"></p>
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-outline-variant/20 shadow-sm">
                                <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Total Savings (25 Thn)</p>
                                <p class="font-headline-lg text-lg text-primary" x-text="result ? formatRupiah(result.totalSavings25Years) : '—'"></p>
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-outline-variant/20 shadow-sm">
                                <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Breakeven</p>
                                <p class="font-headline-lg text-lg text-primary" x-text="result ? result.breakevenYears + ' Thn' : '—'"></p>
                            </div>
                            <div class="bg-white p-3 rounded-lg border border-outline-variant/20 shadow-sm">
                                <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Solar Gen (Tahunan)</p>
                                <p class="font-headline-lg text-lg text-primary" x-text="result ? result.annualKwh + ' kWh' : '—'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
