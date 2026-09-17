@php
    $vaOptions = ['900', '1300', '2200', '3500', '4400', '5500', '6600', '7700', '11000'];
    $areaOptions = ['Jakarta Selatan', 'Jakarta Timur', 'Jakarta Barat', 'Jakarta Utara', 'Jakarta Pusat', 'Luar Jakarta'];

    // Token waktu render (terenkripsi server, tidak bisa dipalsukan client).
    // Dipakai App\Services\SubmissionGuard untuk menolak submit yang terjadi
    // terlalu cepat setelah halaman dibuka -- ciri skrip/bot, bukan manusia.
    $formToken = \App\Services\SubmissionGuard::issueToken();

    // Katalog peralatan listrik dikelola admin di CMS (App\Models\
    // ElectricityAppliance) — watt final tetap divalidasi ulang di server
    // saat submit (App\Services\SavingsEstimator), daftar ini hanya untuk UI.
    $appliances = \App\Models\ElectricityAppliance::activeCatalog()
        ->map(fn ($item) => ['key' => $item->slug, 'label' => $item->name, 'icon' => $item->icon, 'watt' => $item->watt, 'qty' => 0])
        ->values();
@endphp
<section id="kalkulator" class="reveal-element relative z-20 max-w-6xl mx-auto px-6 -mt-32 mb-32">
    <div x-data="calculatorComponent(@js($formToken), @js($appliances))" class="bg-white p-8 md:p-12 shadow-2xl border border-gray-50/50 max-w-5xl mx-auto rounded-lg">
        <div class="text-center mb-10">
            <h2 class="font-headline-xl text-3xl md:text-5xl font-extrabold tracking-tight mb-3 text-primary">Hitung Estimasi Penghematan</h2>
            <p class="font-medium text-base md:text-lg max-w-2xl mx-auto text-secondary">Dapatkan analisis transparan untuk potensi efisiensi energi Anda.</p>
        </div>

        <div class="flex flex-col gap-8">
            {{-- Category Tabs --}}
            <div class="flex justify-center p-1 bg-surface-container-low rounded-lg max-w-md mx-auto border border-outline-variant/30">
                <button type="button" @click="category = 'residential'; resetResult()"
                        :class="category === 'residential' ? 'bg-primary text-white shadow-sm' : 'text-primary hover:bg-white/50'"
                        class="flex-1 py-3 px-6 rounded-md font-bold text-sm transition-all active:scale-95">Residential</button>
                <button type="button" @click="category = 'industrial'; resetResult()"
                        :class="category === 'industrial' ? 'bg-primary text-white shadow-sm' : 'text-primary hover:bg-white/50'"
                        class="flex-1 py-3 px-6 rounded-md font-bold text-sm transition-all active:scale-95">Industrial</button>
            </div>

            <div class="flex flex-col md:flex-row items-stretch gap-12">
                {{-- Input Side --}}
                <div class="md:w-1/2 flex flex-col">
                    {{-- Method cards (residential only) --}}
                    <div x-show="category === 'residential'" class="mb-6">
                        <label class="block font-semibold text-primary mb-4 text-sm">Metode Perhitungan</label>
                        <div class="grid grid-cols-2 gap-4">
                            <button type="button" @click="method = 'bill'; resetResult()"
                                    :class="method === 'bill' ? 'border-primary bg-primary/5' : 'border-transparent bg-surface-container-low hover:border-primary/30'"
                                    class="flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all active:scale-[0.98]">
                                <span class="material-symbols-outlined mb-2 text-primary">receipt_long</span>
                                <span class="text-xs font-bold text-primary">Berdasarkan Tagihan</span>
                            </button>
                            <button type="button" @click="method = 'appliance'; resetResult()"
                                    :class="method === 'appliance' ? 'border-primary bg-primary/5' : 'border-transparent bg-surface-container-low hover:border-primary/30'"
                                    class="flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all active:scale-[0.98]">
                                <span class="material-symbols-outlined mb-2 text-primary/70">kitchen</span>
                                <span class="text-xs font-bold text-primary/70">Berdasarkan Peralatan</span>
                            </button>
                        </div>
                    </div>

                    {{-- Input: By Bill --}}
                    <div x-show="effectiveMethod === 'bill'" class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-primary/70" x-text="category === 'industrial' ? 'Tagihan Listrik Bulanan Pabrik/Gudang (Rp)' : 'Tagihan Listrik Bulanan (Rp)'"></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">Rp</span>
                                <input type="text" x-model="billInput" @input="resetResult()" placeholder="2.500.000"
                                       class="w-full pl-12 pr-4 py-4 bg-surface-container-low border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-lg font-semibold rounded-lg">
                            </div>
                        </div>
                        <div x-show="category === 'residential'" class="space-y-2">
                            <label class="block text-sm font-bold text-primary/70">Kapasitas Daya PLN (VA)</label>
                            <select x-model="vaCapacity" class="w-full px-4 py-4 bg-surface-container-low border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm cursor-pointer rounded-lg">
                                @foreach ($vaOptions as $va)
                                    <option value="{{ $va }}">{{ number_format((int) $va, 0, ',', '.') }} VA</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Input: By Appliance --}}
                    <div x-show="effectiveMethod === 'appliance'" class="space-y-4">
                        <p class="text-sm text-primary/70">Pilih jumlah peralatan listrik di rumah Anda:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-56 overflow-y-auto pr-2">
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
                                        <button type="button" @click="item.qty = Math.max(0, item.qty - 1); resetResult()" class="w-7 h-7 flex items-center justify-center text-primary hover:bg-primary/5">-</button>
                                        <input type="number" min="0" x-model.number="item.qty" @input="resetResult()" class="w-8 h-7 text-center bg-transparent border-none p-0 text-xs font-bold focus:ring-0">
                                        <button type="button" @click="item.qty++; resetResult()" class="w-7 h-7 flex items-center justify-center text-primary hover:bg-primary/5">+</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Lead capture --}}
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-primary/70">Nama Lengkap</label>
                            <input type="text" x-model="lead.name" placeholder="Budi Santoso" class="w-full px-4 py-3.5 bg-surface-container-low border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm rounded-lg">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-primary/70">Nomor WhatsApp</label>
                            <input type="tel" x-model="lead.phone" placeholder="0812..." class="w-full px-4 py-3.5 bg-surface-container-low border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm rounded-lg">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-primary/70">Email <span class="font-normal text-outline">(opsional)</span></label>
                            <input type="email" x-model="lead.email" placeholder="budi@email.com" class="w-full px-4 py-3.5 bg-surface-container-low border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm rounded-lg">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-primary/70">Area</label>
                            <select x-model="lead.area" class="w-full px-4 py-3.5 bg-surface-container-low border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm cursor-pointer rounded-lg">
                                @foreach ($areaOptions as $area)
                                    <option value="{{ $area }}">{{ $area }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Honeypot anti-bot: disembunyikan dari mata & dari urutan tab, jadi
                         tidak pernah terisi manusia. Bot pengisi-otomatis cenderung mengisinya,
                         dan submit yang field ini terisi akan ditolak diam-diam di server.
                         JANGAN dihapus atau diberi label yang terlihat pengunjung. --}}
                    <div aria-hidden="true" class="absolute w-px h-px overflow-hidden -left-[9999px] top-auto">
                        <label>Website</label>
                        <input type="text" x-model="honeypot" tabindex="-1" autocomplete="off">
                    </div>

                    <p x-show="error" x-cloak x-text="error" class="text-sm text-error font-medium mt-4"></p>
                    <p x-show="submitted" x-cloak class="text-sm text-primary font-medium mt-4">Estimasi terkirim — tim kami akan menghubungi Anda. Lihat hasilnya di samping.</p>

                    <button type="button" @click="calculate()" :disabled="submitting"
                            class="btn-fill text-white py-4 font-bold hover:shadow-lg active:scale-[0.98] transition-all mt-6 flex items-center justify-center gap-2 bg-primary-container w-full rounded-lg disabled:opacity-70">
                        <span x-text="submitting ? 'Mengirim...' : 'Dapatkan Hasil Analisis'"></span>
                        <span class="material-symbols-outlined" x-show="!submitting">arrow_forward</span>
                    </button>
                </div>

                {{-- Output Side --}}
                <div class="md:w-1/2 bg-surface-container-low flex flex-col justify-center border border-primary/10 rounded-lg relative overflow-hidden">
                    <div class="w-full h-full p-6 flex flex-col gap-6 transition-all duration-300" :class="result ? '' : 'blur-sm pointer-events-none select-none'">
                        <div class="flex justify-between items-center">
                            <h3 class="font-headline-lg text-sm text-primary">Proyeksi Hemat vs Investasi</h3>
                            <div class="flex gap-3">
                                <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-primary"></div><span class="text-[10px] font-medium">Hemat</span></div>
                                <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-outline-variant"></div><span class="text-[10px] font-medium">Investasi</span></div>
                            </div>
                        </div>

                        <div class="flex-1 relative min-h-[160px]">
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
                                <span>Thn 0</span><span>Thn 5</span><span>Thn 10</span><span>Thn 15</span><span>Thn 20</span><span>Thn 25</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3" data-testid="calculator-result">
                            <div class="bg-white p-3 rounded-xl border border-outline-variant/20 shadow-sm">
                                <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Est. Hemat (Tahun 1)</p>
                                <p class="font-headline-lg text-lg font-bold text-primary" x-text="result ? formatRupiah(result.savingsYear1) : '—'"></p>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-outline-variant/20 shadow-sm">
                                <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Total Hemat (25 Thn)</p>
                                <p class="font-headline-lg text-lg font-bold text-primary" x-text="result ? formatRupiah(result.totalSavings25Years) : '—'"></p>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-outline-variant/20 shadow-sm">
                                <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Breakeven</p>
                                <p class="font-headline-lg text-lg font-bold text-primary" x-text="result ? result.breakevenYears + ' Thn' : '—'"></p>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-outline-variant/20 shadow-sm">
                                <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Produksi Surya (Tahunan)</p>
                                <p class="font-headline-lg text-lg font-bold text-primary" x-text="result ? result.annualKwh + ' kWh' : '—'"></p>
                            </div>
                        </div>
                    </div>

                    <div x-show="!result" x-cloak x-transition.opacity
                         class="absolute inset-0 flex flex-col items-center justify-center text-center gap-3 p-6 bg-white/40">
                        <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-2xl">lock</span>
                        </div>
                        <p class="font-headline-lg text-sm font-bold text-primary max-w-[220px]">Lengkapi data Anda untuk melihat hasil analisis</p>
                        <p class="text-xs text-secondary max-w-[220px]">Isi Nama &amp; Nomor WhatsApp di samping, lalu klik "Dapatkan Hasil Analisis".</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
