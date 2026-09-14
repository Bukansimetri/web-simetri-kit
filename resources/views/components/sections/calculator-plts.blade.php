@php
    $pltsAreaOptions = ['Jakarta Selatan', 'Jakarta Timur', 'Jakarta Barat', 'Jakarta Utara', 'Jakarta Pusat', 'Luar Jakarta'];
@endphp
<section id="kalkulator-plts" class="reveal-element max-w-6xl mx-auto px-6 py-20">
    <div x-data="pltsCalculatorComponent()" class="bg-white p-8 md:p-12 shadow-2xl border border-gray-50/50 max-w-6xl mx-auto rounded-lg">
        <div class="text-center mb-10">
            <span class="inline-block text-xs font-bold uppercase tracking-wider text-primary bg-primary/5 border border-primary/20 rounded-full px-3 py-1 mb-4">Kalkulator Detail (Opsional)</span>
            <h2 class="font-headline-xl text-3xl md:text-5xl font-extrabold tracking-tight mb-3 text-primary">Kalkulator Detail Sistem PLTS</h2>
            <p class="font-medium text-base md:text-lg max-w-2xl mx-auto text-secondary">Tentukan kebutuhan daya, kondisi atap, dan pilih komponen satu per satu untuk estimasi yang lebih rinci.</p>
        </div>

        <div class="flex gap-1 p-1 bg-surface-container-low rounded-full border border-outline-variant/30 overflow-x-auto mb-10">
            <template x-for="n in visibleSteps" :key="n">
                <button type="button"
                        @click="goto(n)"
                        :disabled="n > maxStep"
                        :class="{
                            'bg-primary text-white shadow-sm': n === step,
                            'text-primary hover:bg-white/60': n !== step && n <= maxStep,
                            'text-outline-variant cursor-not-allowed': n > maxStep,
                        }"
                        class="flex-1 min-w-[92px] whitespace-nowrap text-[11px] md:text-xs font-bold rounded-full px-3 py-2.5 transition-all">
                    <span x-text="n + '. ' + stepLabels[n - 1]"></span>
                </button>
            </template>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <div class="lg:col-span-2 flex flex-col gap-6">

                <div x-show="step === 1" x-cloak>
                    <h3 class="font-headline-lg text-xl font-bold text-primary mb-1">Pemakaian Listrik</h3>
                    <p class="text-sm text-secondary mb-6">Pilih cara input sesuai data yang tersedia. Kalau ragu, pakai daya PLN dulu.</p>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-primary/70 mb-3">Cara menghitung</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="mode = 'pln'" :class="mode === 'pln' ? 'bg-primary text-white border-primary' : 'bg-white text-primary border-outline-variant hover:border-primary/40'" class="text-sm font-bold rounded-full px-4 py-2.5 border transition-all">Dari daya PLN</button>
                            <button type="button" @click="mode = 'kwh'" :class="mode === 'kwh' ? 'bg-primary text-white border-primary' : 'bg-white text-primary border-outline-variant hover:border-primary/40'" class="text-sm font-bold rounded-full px-4 py-2.5 border transition-all">Dari pemakaian harian</button>
                            <button type="button" @click="mode = 'app'" :class="mode === 'app' ? 'bg-primary text-white border-primary' : 'bg-white text-primary border-outline-variant hover:border-primary/40'" class="text-sm font-bold rounded-full px-4 py-2.5 border transition-all">Dari daftar alat</button>
                        </div>
                    </div>

                    <div x-show="mode === 'pln'" class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-primary/70 mb-2">Daya terpasang PLN</label>
                            <select x-model="plnVa" class="w-full px-4 py-3.5 bg-surface-container-low border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm rounded-lg">
                                <option value="900">900 VA</option>
                                <option value="1300">1.300 VA</option>
                                <option value="2200">2.200 VA</option>
                                <option value="3500">3.500 VA</option>
                                <option value="4400">4.400 VA</option>
                                <option value="5500">5.500 VA</option>
                                <option value="6600">6.600 VA</option>
                                <option value="7700">7.700 VA</option>
                                <option value="10600">10.600 VA</option>
                                <option value="13200">13.200 VA</option>
                                <option value="16500">16.500 VA</option>
                                <option value="23000">23.000 VA</option>
                            </select>
                        </div>
                        <div>
                            <label class="flex justify-between text-sm font-bold text-primary/70 mb-2">
                                <span>Faktor beban rata-rata</span>
                                <span class="text-primary" x-text="loadFactor + '%'"></span>
                            </label>
                            <input type="range" min="10" max="60" step="5" x-model.number="loadFactor" :style="'--pct:' + pct(loadFactor, 10, 60) + '%'">
                            <p class="text-xs text-secondary mt-2">Persentase dari daya terpasang yang benar-benar terpakai sepanjang hari. Rumah tangga umum 25-35%.</p>
                        </div>
                    </div>

                    <div x-show="mode === 'kwh'" class="space-y-5">
                        <div>
                            <label class="flex justify-between text-sm font-bold text-primary/70 mb-2">
                                <span>Pemakaian listrik</span>
                                <span class="text-primary" x-text="formatNumber(dailyKwh, 1) + ' kWh/hari'"></span>
                            </label>
                            <input type="range" min="1" max="60" step="0.5" x-model.number="dailyKwh" :style="'--pct:' + pct(dailyKwh, 1, 60) + '%'">
                            <p class="text-xs text-secondary mt-2">Bisa dilihat di PLN Mobile, atau tagihan bulanan (kWh) dibagi 30.</p>
                        </div>
                    </div>

                    <div x-show="mode === 'app'">
                        <div class="hidden md:grid grid-cols-[1.6fr_0.8fr_0.5fr_0.7fr_36px] gap-2 text-[11px] font-bold text-secondary uppercase tracking-wide mb-2">
                            <div>Nama alat</div><div>Watt</div><div>Unit</div><div>Jam/hari</div><div></div>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(item, index) in appliances" :key="index">
                                <div class="grid grid-cols-2 md:grid-cols-[1.6fr_0.8fr_0.5fr_0.7fr_36px] gap-2 items-center bg-surface-container-low rounded-lg p-2 md:p-0 md:bg-transparent">
                                    <input type="text" x-model="item.name" class="col-span-2 md:col-span-1 px-3 py-2 bg-white md:bg-surface-container-low border border-transparent focus:border-primary text-sm rounded-lg" aria-label="Nama alat">
                                    <input type="number" min="0" x-model.number="item.w" class="px-3 py-2 bg-white md:bg-surface-container-low border border-transparent focus:border-primary text-sm rounded-lg" aria-label="Watt">
                                    <input type="number" min="0" x-model.number="item.q" class="px-3 py-2 bg-white md:bg-surface-container-low border border-transparent focus:border-primary text-sm rounded-lg" aria-label="Unit">
                                    <input type="number" min="0" max="24" step="0.5" x-model.number="item.h" class="px-3 py-2 bg-white md:bg-surface-container-low border border-transparent focus:border-primary text-sm rounded-lg" aria-label="Jam per hari">
                                    <button type="button" @click="removeAppliance(index)" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-secondary hover:text-error hover:border-error/40 hover:bg-error/5" aria-label="Hapus alat">
                                        <span class="material-symbols-outlined text-lg">close</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addAppliance()" class="w-full mt-3 py-3 border border-dashed border-primary/40 text-primary text-sm font-bold rounded-lg hover:bg-primary/5 transition-all">+ Tambah alat</button>
                        <div class="flex justify-between items-center mt-4 pt-4 border-t border-outline-variant/30 text-sm font-bold text-primary">
                            <span>Total pemakaian harian</span>
                            <span class="text-primary" x-text="formatNumber(totalDailyKwh, 1) + ' kWh'"></span>
                        </div>
                    </div>
                </div>

                <div x-show="step === 2" x-cloak>
                    <h3 class="font-headline-lg text-xl font-bold text-primary mb-1">Kondisi Pemakaian</h3>
                    <p class="text-sm text-secondary mb-6">Menentukan berapa banyak dari pemakaian harian yang realistis dicover panel.</p>

                    <div class="bg-primary/5 border-l-4 border-primary rounded-lg px-4 py-3 text-sm text-secondary mb-6 leading-relaxed">
                        <strong class="text-primary">Kenapa ini penting.</strong> Sejak Permen ESDM 2/2024, mekanisme ekspor-impor listrik ke PLN ditiadakan - kelebihan produksi di luar jam pemakaian tidak lagi mengurangi tagihan, kecuali disimpan dengan baterai.
                    </div>

                    <div class="mb-6">
                        <label class="flex justify-between text-sm font-bold text-primary/70 mb-2">
                            <span>Porsi pemakaian di siang hari</span>
                            <span class="text-primary" x-text="dayShare + '%'"></span>
                        </label>
                        <input type="range" min="20" max="100" step="5" x-model.number="dayShare" :style="'--pct:' + pct(dayShare, 20, 100) + '%'">
                        <p class="text-xs text-secondary mt-2">Rumah yang ditinggal kerja biasanya rendah (30-40%). Rumah dengan penghuni seharian bisa 70% ke atas.</p>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-4 border-t border-outline-variant/30">
                        <div>
                            <p class="text-sm font-bold text-primary">Pakai baterai (sistem hybrid)</p>
                            <p class="text-xs text-secondary mt-1">Menyimpan produksi siang untuk dipakai malam, sehingga seluruh pemakaian harian bisa dicover.</p>
                        </div>
                        <button type="button" role="switch" :aria-checked="useBattery.toString()" @click="useBattery = !useBattery"
                                :class="useBattery ? 'bg-primary' : 'bg-outline-variant'"
                                class="relative w-11 h-6 rounded-full shrink-0 transition-colors">
                            <span :class="useBattery ? 'translate-x-5' : 'translate-x-0.5'" class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"></span>
                        </button>
                    </div>
                </div>

                <div x-show="step === 3" x-cloak>
                    <h3 class="font-headline-lg text-xl font-bold text-primary mb-1">Kondisi Atap</h3>
                    <p class="text-sm text-secondary mb-6">Jenis atap menentukan harga bracket. Luas atap menentukan pilihan panel mana yang muat.</p>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-primary/70 mb-3">Jenis atap</label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="key in ['genteng','metal','dak']" :key="key">
                                <button type="button" @click="roof = key"
                                        :class="roof === key ? 'bg-primary text-white border-primary' : 'bg-white text-primary border-outline-variant hover:border-primary/40'"
                                        class="text-sm font-bold rounded-full px-4 py-2.5 border transition-all" x-text="roofOptions[key].label"></button>
                            </template>
                        </div>
                        <p class="text-xs text-secondary mt-2" x-text="roofOptions[roof].desc + ' Estimasi ' + formatRupiah(roofOptions[roof].rp) + ' per m2.'"></p>
                    </div>

                    <div>
                        <label class="flex justify-between text-sm font-bold text-primary/70 mb-2">
                            <span>Luas atap yang bisa dipakai</span>
                            <span class="text-primary" x-text="formatNumber(roofArea) + ' m2'"></span>
                        </label>
                        <input type="range" min="5" max="150" step="1" x-model.number="roofArea" :style="'--pct:' + pct(roofArea, 5, 150) + '%'">
                        <p class="text-xs text-secondary mt-2">Hanya bagian yang menghadap matahari dan bebas bayangan. Kalau belum disurvei, isi perkiraan kasar dulu.</p>
                    </div>
                </div>

                <div x-show="step === 4" x-cloak>
                    <h3 class="font-headline-lg text-xl font-bold text-primary mb-1">Pilih Panel Surya</h3>
                    <p class="text-sm text-secondary mb-6" x-text="'Kebutuhan ' + formatNumber(calc.wpNeed) + ' Wp. Atap tersedia ' + formatNumber(roofArea) + ' m2. Pilihan yang tidak muat ditandai dan tidak bisa dipilih.'"></p>

                    <div class="space-y-3">
                        <template x-for="row in panelRows()" :key="row.panel.m">
                            <button type="button" @click="!row.off && (panel = row.panel)" :disabled="row.off"
                                    :class="{
                                        'border-primary bg-primary/5': panel === row.panel,
                                        'opacity-50 cursor-not-allowed bg-surface-container-low': row.off,
                                        'border-outline-variant hover:border-primary/40': panel !== row.panel && !row.off,
                                    }"
                                    class="w-full flex items-start justify-between gap-4 text-left border-2 rounded-xl p-4 transition-all">
                                <div class="flex items-start gap-3">
                                    <span :class="panel === row.panel ? 'border-primary' : 'border-outline-variant'" class="w-5 h-5 rounded-full border-2 mt-0.5 shrink-0 flex items-center justify-center">
                                        <span x-show="panel === row.panel" class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                                    </span>
                                    <div>
                                        <div class="flex items-center flex-wrap gap-2">
                                            <span class="font-bold text-sm text-primary" x-text="row.panel.m"></span>
                                            <span x-show="row.off" class="text-[10px] font-bold uppercase bg-error-container text-on-error-container rounded-full px-2 py-0.5">Tidak Muat</span>
                                            <span x-show="!row.off && row.isBest" class="text-[10px] font-bold uppercase bg-primary/10 text-primary rounded-full px-2 py-0.5">Termurah</span>
                                        </div>
                                        <p class="text-xs text-secondary mt-0.5" x-text="row.panel.b + ' - ' + row.panel.type + ' ' + row.panel.wp + ' Wp'"></p>
                                        <p class="text-xs text-secondary mt-1.5">
                                            <b class="text-primary" x-text="row.count + ' lembar'"></b>
                                            <span x-text="' - total ' + formatNumber(row.wp) + ' Wp - butuh ' + formatNumber(row.area, 1) + ' m2'"></span>
                                            <span x-show="row.off" class="text-error" x-text="' - lebih ' + formatNumber(row.area - roofArea, 1) + ' m2 dari atap tersedia'"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-headline-lg text-sm font-bold text-primary" x-text="formatRupiah(row.cost)"></p>
                                    <p class="text-[11px] text-secondary mt-0.5" x-text="formatRupiah(row.panel.rp) + '/Wp'"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                    <div x-show="panelRows().every(r => r.off)" x-cloak class="mt-4 bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-lg px-4 py-3 text-sm text-amber-800">
                        <strong>Tidak ada panel yang muat.</strong> Turunkan porsi pemakaian siang di langkah 2, atau perbesar luas atap di langkah 3.
                    </div>
                </div>

                <div x-show="step === 5" x-cloak>
                    <h3 class="font-headline-lg text-xl font-bold text-primary mb-1">Pilih Inverter</h3>
                    <p class="text-sm text-secondary mb-6" x-text="(useBattery ? 'Array ' + formatNumber(calc.arrayWp) + ' Wp dengan baterai, hanya inverter hybrid ditampilkan. ' : 'Array ' + formatNumber(calc.arrayWp) + ' Wp tanpa baterai. ') + 'Kapasitas minimal ' + formatNumber(calc.arrayWp / 1000, 1) + ' kVA.'"></p>

                    <div class="space-y-3">
                        <template x-for="row in inverterRows()" :key="row.inv.m">
                            <button type="button" @click="!row.off && (inverter = row.inv)" :disabled="row.off"
                                    :class="{
                                        'border-primary bg-primary/5': inverter === row.inv,
                                        'opacity-50 cursor-not-allowed bg-surface-container-low': row.off,
                                        'border-outline-variant hover:border-primary/40': inverter !== row.inv && !row.off,
                                    }"
                                    class="w-full flex items-start justify-between gap-4 text-left border-2 rounded-xl p-4 transition-all">
                                <div class="flex items-start gap-3">
                                    <span :class="inverter === row.inv ? 'border-primary' : 'border-outline-variant'" class="w-5 h-5 rounded-full border-2 mt-0.5 shrink-0 flex items-center justify-center">
                                        <span x-show="inverter === row.inv" class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                                    </span>
                                    <div>
                                        <div class="flex items-center flex-wrap gap-2">
                                            <span class="font-bold text-sm text-primary" x-text="row.inv.m"></span>
                                            <span x-show="row.off" class="text-[10px] font-bold uppercase bg-error-container text-on-error-container rounded-full px-2 py-0.5">Kapasitas Kurang</span>
                                            <span x-show="!row.off && row.isBest" class="text-[10px] font-bold uppercase bg-primary/10 text-primary rounded-full px-2 py-0.5">Paling Pas</span>
                                        </div>
                                        <p class="text-xs text-secondary mt-0.5" x-text="row.inv.b + ' - ' + (row.inv.hy ? 'Hybrid ' + row.inv.v : 'Off-grid')"></p>
                                        <p class="text-xs text-secondary mt-1.5" x-text="row.off ? ('Kurang ' + formatNumber(row.need - row.inv.kva, 1) + ' kVA untuk array ini') : ('Sisa headroom ' + formatNumber((row.inv.kva - row.need) / row.need * 100) + '% dari array')"></p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-headline-lg text-sm font-bold text-primary" x-text="formatRupiah(row.inv.rp)"></p>
                                    <p class="text-[11px] text-secondary mt-0.5" x-text="formatNumber(row.inv.kva, 1) + ' kVA'"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="step === 6" x-cloak>
                    <h3 class="font-headline-lg text-xl font-bold text-primary mb-1">Pilih Baterai</h3>
                    <p class="text-sm text-secondary mb-6" x-text="'Pemakaian malam sekitar ' + formatNumber(calc.nightKwh, 1) + ' kWh. Dengan depth of discharge 80%, jumlah unit dihitung otomatis.'"></p>

                    <div class="space-y-3">
                        <template x-for="row in batteryRows()" :key="row.batt.m">
                            <button type="button" @click="battery = row.batt"
                                    :class="battery === row.batt ? 'border-primary bg-primary/5' : 'border-outline-variant hover:border-primary/40'"
                                    class="w-full flex items-start justify-between gap-4 text-left border-2 rounded-xl p-4 transition-all">
                                <div class="flex items-start gap-3">
                                    <span :class="battery === row.batt ? 'border-primary' : 'border-outline-variant'" class="w-5 h-5 rounded-full border-2 mt-0.5 shrink-0 flex items-center justify-center">
                                        <span x-show="battery === row.batt" class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                                    </span>
                                    <div>
                                        <span class="font-bold text-sm text-primary" x-text="row.batt.m"></span>
                                        <p class="text-xs text-secondary mt-0.5" x-text="row.batt.b"></p>
                                        <p class="text-xs text-secondary mt-1.5">
                                            <b class="text-primary" x-text="row.units + ' unit'"></b>
                                            <span x-text="' - kapasitas total ' + formatNumber(row.units * row.batt.kwh, 1) + ' kWh - terpakai ' + formatNumber(row.units * row.batt.kwh * 0.8, 1) + ' kWh'"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-headline-lg text-sm font-bold text-primary" x-text="formatRupiah(row.cost)"></p>
                                    <p class="text-[11px] text-secondary mt-0.5" x-text="formatRupiah(row.batt.rp) + '/unit'"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="step === 7" x-cloak>
                    <h3 class="font-headline-lg text-xl font-bold text-primary mb-1">Ringkasan</h3>
                    <p class="text-sm text-secondary mb-6">Klik langkah manapun di atas untuk mengubah pilihan.</p>

                    <h4 class="text-sm font-bold text-primary mb-3">Konfigurasi Sistem</h4>
                    <div class="space-y-0 mb-6">
                        <div class="flex justify-between py-2.5 border-b border-outline-variant/20 text-sm">
                            <span class="text-secondary">Atap</span>
                            <span class="font-semibold text-primary text-right" x-text="roofOptions[roof].label + ' - ' + formatNumber(roofArea) + ' m2'"></span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-outline-variant/20 text-sm">
                            <span class="text-secondary">Panel</span>
                            <span class="font-semibold text-primary text-right" x-text="panel ? (calc.count + 'x ' + panel.b + ' ' + panel.m) : '-'"></span>
                        </div>
                        <div class="flex justify-between py-2.5 border-b border-outline-variant/20 text-sm">
                            <span class="text-secondary">Inverter</span>
                            <span class="font-semibold text-primary text-right" x-text="inverter ? (inverter.b + ' ' + inverter.m) : '-'"></span>
                        </div>
                        <div x-show="useBattery" class="flex justify-between py-2.5 text-sm">
                            <span class="text-secondary">Baterai</span>
                            <span class="font-semibold text-primary text-right" x-text="battery ? (calc.batteryUnits + 'x ' + battery.b + ' ' + battery.m) : '-'"></span>
                        </div>
                    </div>

                    <div class="bg-surface-container-low rounded-xl p-5 md:p-6">
                        <h4 class="text-sm font-bold text-primary mb-1">Isi data Anda untuk melihat rincian biaya & estimasi hemat</h4>
                        <p class="text-xs text-secondary mb-4">Tim kami juga akan menghubungi Anda untuk konsultasi lebih lanjut.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-primary/70 mb-1.5">Nama Lengkap</label>
                                <input type="text" x-model="lead.name" placeholder="Budi Santoso" class="w-full px-4 py-3 bg-white border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-primary/70 mb-1.5">Nomor WhatsApp</label>
                                <input type="tel" x-model="lead.phone" placeholder="0812..." class="w-full px-4 py-3 bg-white border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-primary/70 mb-1.5">Email <span class="font-normal text-outline">(opsional)</span></label>
                                <input type="email" x-model="lead.email" placeholder="budi@email.com" class="w-full px-4 py-3 bg-white border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-primary/70 mb-1.5">Area</label>
                                <select x-model="lead.area" class="w-full px-4 py-3 bg-white border border-transparent focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm rounded-lg">
                                    @foreach ($pltsAreaOptions as $area)
                                        <option value="{{ $area }}">{{ $area }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p x-show="error" x-cloak x-text="error" class="text-sm text-error font-medium mt-3"></p>
                        <p x-show="submitted" x-cloak class="text-sm text-primary font-medium mt-3">Estimasi terkirim - tim kami akan menghubungi Anda.</p>
                        <button type="button" @click="unlockResults()" :disabled="submitting"
                                class="btn-fill text-white py-3.5 font-bold hover:shadow-lg active:scale-[0.98] transition-all mt-4 flex items-center justify-center gap-2 bg-primary-container w-full rounded-lg disabled:opacity-70">
                            <span x-text="submitting ? 'Mengirim...' : (resultsUnlocked ? 'Perbarui Hasil' : 'Dapatkan Hasil & Estimasi')"></span>
                            <span class="material-symbols-outlined" x-show="!submitting">arrow_forward</span>
                        </button>
                    </div>

                    <div class="relative mt-6 overflow-hidden rounded-xl border border-primary/10">
                        <div class="transition-all duration-300" :class="resultsUnlocked ? '' : 'blur-sm pointer-events-none select-none'">
                            <div class="bg-surface-container-low p-5 md:p-6">
                                <h4 class="text-sm font-bold text-primary mb-3">Rincian Biaya</h4>
                                <div class="space-y-0">
                                    <div class="flex justify-between py-2 border-b border-outline-variant/20 text-sm">
                                        <span class="text-secondary" x-text="'Panel surya (' + formatNumber(calc.arrayWp) + ' Wp x ' + formatRupiah(panel ? panel.rp : 0) + ')'"></span>
                                        <span class="font-semibold text-primary" x-text="formatRupiah(costs.panel)"></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-outline-variant/20 text-sm">
                                        <span class="text-secondary" x-text="'Bracket atap (' + formatNumber(calc.m2, 1) + ' m2 x ' + formatRupiah(roofOptions[roof].rp) + ')'"></span>
                                        <span class="font-semibold text-primary" x-text="formatRupiah(costs.bracket)"></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-outline-variant/20 text-sm">
                                        <span class="text-secondary" x-text="'Inverter ' + (inverter ? inverter.m : '')"></span>
                                        <span class="font-semibold text-primary" x-text="formatRupiah(costs.inv)"></span>
                                    </div>
                                    <div x-show="useBattery" class="flex justify-between py-2 border-b border-outline-variant/20 text-sm">
                                        <span class="text-secondary" x-text="'Baterai ' + calc.batteryUnits + ' unit'"></span>
                                        <span class="font-semibold text-primary" x-text="formatRupiah(costs.batt)"></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b border-outline-variant/20 text-sm">
                                        <span class="text-secondary">Kabel dan konektor</span>
                                        <span class="font-semibold text-primary" x-text="formatRupiah(costs.kabel)"></span>
                                    </div>
                                    <div class="flex justify-between py-2 text-sm">
                                        <span class="text-secondary" x-text="'Jasa instalasi (' + formatNumber(calc.arrayWp) + ' W x ' + formatRupiah(1500) + ')'"></span>
                                        <span class="font-semibold text-primary" x-text="formatRupiah(costs.jasa)"></span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-baseline mt-4 pt-4 border-t-2 border-primary">
                                    <span class="font-headline-lg text-sm font-bold text-primary">Estimasi Total</span>
                                    <span class="font-headline-lg text-2xl font-bold text-primary" x-text="formatRupiah(costs.total)"></span>
                                </div>
                            </div>

                            <div class="p-5 md:p-6 border-t border-outline-variant/20">
                                <h4 class="text-sm font-bold text-primary mb-4">Asumsi Perhitungan Penghematan</h4>
                                <div class="space-y-5">
                                    <div>
                                        <label class="flex justify-between text-xs font-bold text-primary/70 mb-2">
                                            <span>Tarif listrik PLN</span>
                                            <span class="text-primary" x-text="formatRupiah(tariff) + '/kWh'"></span>
                                        </label>
                                        <input type="range" min="1000" max="2500" step="25" x-model.number="tariff" :style="'--pct:' + pct(tariff, 1000, 2500) + '%'">
                                    </div>
                                    <div>
                                        <label class="flex justify-between text-xs font-bold text-primary/70 mb-2">
                                            <span>Kenaikan tarif per tahun</span>
                                            <span class="text-primary" x-text="formatNumber(inflation, 1) + '%'"></span>
                                        </label>
                                        <input type="range" min="0" max="8" step="0.5" x-model.number="inflation" :style="'--pct:' + pct(inflation, 0, 8) + '%'">
                                    </div>
                                    <div>
                                        <label class="flex justify-between text-xs font-bold text-primary/70 mb-2">
                                            <span>Penurunan performa panel per tahun</span>
                                            <span class="text-primary" x-text="formatNumber(degradation, 2) + '%'"></span>
                                        </label>
                                        <input type="range" min="0" max="1.5" step="0.05" x-model.number="degradation" :style="'--pct:' + pct(degradation, 0, 1.5) + '%'">
                                    </div>
                                    <div class="flex justify-between items-center gap-4">
                                        <div>
                                            <p class="text-xs font-bold text-primary">Hitung biaya penggantian komponen</p>
                                            <p class="text-[11px] text-secondary mt-1">Inverter diganti tahun ke-12, baterai tahun ke-10.</p>
                                        </div>
                                        <button type="button" role="switch" :aria-checked="includeReplacement.toString()" @click="includeReplacement = !includeReplacement"
                                                :class="includeReplacement ? 'bg-primary' : 'bg-outline-variant'"
                                                class="relative w-11 h-6 rounded-full shrink-0 transition-colors">
                                            <span :class="includeReplacement ? 'translate-x-5' : 'translate-x-0.5'" class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 md:p-6 border-t border-outline-variant/20">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-sm font-bold text-primary">Akumulasi Penghematan vs Investasi</h4>
                                    <div class="flex gap-3">
                                        <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-primary-container"></div><span class="text-[10px] font-medium text-secondary">Hemat</span></div>
                                        <div class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-outline-variant"></div><span class="text-[10px] font-medium text-secondary">Investasi</span></div>
                                    </div>
                                </div>
                                <div x-html="chartSvg()"></div>
                                <div class="grid grid-cols-2 gap-3 mt-4">
                                    <div class="bg-white p-3 rounded-xl border border-outline-variant/20 shadow-sm">
                                        <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Hemat Tahun Pertama</p>
                                        <p class="font-headline-lg text-lg font-bold text-primary" x-text="formatRupiah(savings.y1)"></p>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-outline-variant/20 shadow-sm">
                                        <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Total Hemat 25 Tahun</p>
                                        <p class="font-headline-lg text-lg font-bold text-primary" x-text="formatRupiah(savings.total)"></p>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border-2 border-primary shadow-sm">
                                        <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Balik Modal</p>
                                        <p class="font-headline-lg text-lg font-bold text-primary" x-text="savings.breakeven ? formatNumber(savings.breakeven, 1) + ' Thn' : '&gt; 25 Thn'"></p>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-outline-variant/20 shadow-sm">
                                        <p class="text-[10px] font-bold text-outline uppercase tracking-wider mb-1">Produksi Terpakai/Thn</p>
                                        <p class="font-headline-lg text-lg font-bold text-primary" x-text="formatNumber(savings.genYear) + ' kWh'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-show="!resultsUnlocked" x-cloak x-transition.opacity
                             class="absolute inset-0 flex flex-col items-center justify-center text-center gap-3 p-6 bg-white/40">
                            <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-2xl">lock</span>
                            </div>
                            <p class="font-headline-lg text-sm font-bold text-primary max-w-[260px]">Isi data Anda di atas untuk melihat rincian biaya & estimasi hemat</p>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 border-l-4 border-l-amber-500 rounded-lg px-4 py-3 text-xs text-amber-800 leading-relaxed mt-6">
                        <strong>Catatan:</strong> Harga model panel, merk inverter, dan harga bracket di kalkulator ini masih data contoh - sesuaikan dengan katalog & harga aktual sebelum dikirim ke pelanggan sebagai penawaran resmi.
                    </div>
                </div>

                <div class="flex justify-between gap-3 pt-2">
                    <button type="button" @click="back()" :disabled="visibleSteps.indexOf(step) === 0"
                            class="border border-outline-variant text-primary font-bold text-sm px-6 py-3 rounded-lg hover:bg-surface-container-low transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        Kembali
                    </button>
                    <button type="button" @click="next()" x-show="!isLastStep" :disabled="!canProceed"
                            class="btn-fill text-white font-bold text-sm px-6 py-3 rounded-lg bg-primary-container hover:shadow-lg transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                        <span x-text="isSecondToLastStep ? 'Lihat Ringkasan' : 'Lanjut'"></span>
                    </button>
                </div>
            </div>

            <aside class="lg:sticky lg:top-24 flex flex-col gap-4">
                <div class="bg-primary text-white rounded-xl p-6">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-white/60 mb-3">Kebutuhan Sistem</p>
                    <p class="font-headline-xl text-3xl font-bold" x-text="formatNumber(panel ? calc.arrayWp : calc.wpNeed) + ' Wp'"></p>
                    <p class="text-xs text-white/60 mt-1" x-text="panel ? ('Terpasang: ' + calc.count + ' lembar ' + panel.m) : 'Kebutuhan minimum'"></p>
                    <div class="grid grid-cols-2 gap-4 mt-5 pt-5 border-t border-white/15">
                        <div>
                            <p class="font-headline-lg text-lg font-bold" x-text="formatNumber(calc.cov, 1)"></p>
                            <p class="text-[11px] text-white/60 mt-0.5">kWh/hari dicover</p>
                        </div>
                        <div>
                            <p class="font-headline-lg text-lg font-bold" x-text="formatNumber(calc.m2, 1)"></p>
                            <p class="text-[11px] text-white/60 mt-0.5">m2 atap terpakai</p>
                        </div>
                    </div>
                    <div class="mt-5 pt-4 border-t border-white/15 space-y-2 text-[11px] text-white/70">
                        <div class="flex justify-between gap-2"><span>Pemakaian harian</span><b class="text-white" x-text="formatNumber(calc.t, 1) + ' kWh'"></b></div>
                        <div class="flex justify-between gap-2"><span x-text="useBattery ? 'Dicover penuh' : 'Porsi siang ' + dayShare + '%'"></span><b class="text-white" x-text="formatNumber(calc.cov, 1) + ' kWh'"></b></div>
                        <div class="flex justify-between gap-2"><span>Dibagi 4,5 jam matahari</span><b class="text-white" x-text="formatNumber(calc.cov * 1000 / 4.5) + ' Wp'"></b></div>
                        <div class="flex justify-between gap-2"><span>Dibagi performance ratio 0,78</span><b class="text-white" x-text="formatNumber(calc.wpNeed) + ' Wp'"></b></div>
                    </div>
                </div>
                <div class="bg-surface-container-low rounded-xl p-6">
                    <p class="text-sm font-bold text-primary mb-3">Pilihan Kamu</p>
                    <div class="space-y-0">
                        <div class="flex justify-between py-2 border-b border-outline-variant/20 text-xs">
                            <span class="text-secondary">Atap</span>
                            <span class="font-semibold text-primary text-right" x-text="roofOptions[roof].label + ' - ' + formatNumber(roofArea) + ' m2'"></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-outline-variant/20 text-xs">
                            <span class="text-secondary">Panel</span>
                            <span :class="panel ? 'font-semibold text-primary' : 'italic text-outline'" class="text-right" x-text="panel ? (calc.count + 'x ' + panel.m) : 'belum dipilih'"></span>
                        </div>
                        <div class="flex justify-between py-2 text-xs" :class="useBattery ? 'border-b border-outline-variant/20' : ''">
                            <span class="text-secondary">Inverter</span>
                            <span :class="inverter ? 'font-semibold text-primary' : 'italic text-outline'" class="text-right" x-text="inverter ? inverter.m : 'belum dipilih'"></span>
                        </div>
                        <div x-show="useBattery" class="flex justify-between py-2 text-xs">
                            <span class="text-secondary">Baterai</span>
                            <span :class="battery ? 'font-semibold text-primary' : 'italic text-outline'" class="text-right" x-text="battery ? (calc.batteryUnits + 'x ' + battery.m) : 'belum dipilih'"></span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
    #kalkulator-plts input[type=range] {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 5px;
        border-radius: 9999px;
        background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-primary) var(--pct, 50%), var(--color-outline-variant) var(--pct, 50%));
        outline-offset: 4px;
    }
    #kalkulator-plts input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--color-primary);
        border: 3px solid #fff;
        cursor: pointer;
        box-shadow: 0 1px 4px rgba(16, 18, 20, .28);
    }
    #kalkulator-plts input[type=range]::-moz-range-thumb {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--color-primary);
        border: 3px solid #fff;
        cursor: pointer;
    }
</style>
