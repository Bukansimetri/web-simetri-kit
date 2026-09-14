/**
 * Kalkulator Detail Sistem PLTS (wizard). Adaptasi dari mockup wizard
 * kebutuhan PLTS yang dibuat sebelumnya di sesi desain terpisah - logic
 * perhitungan (sizing Wp, pemilihan panel/inverter/baterai, proyeksi
 * penghematan 25 tahun) dipertahankan sama, hanya dibungkus jadi Alpine
 * component dan direstyle memakai design token situs ini.
 *
 * Katalog panel/inverter/baterai/harga bracket di bawah ini MASIH DATA
 * CONTOH (lihat catatan di bagian Ringkasan) - ganti dengan data asli
 * sebelum dipakai sebagai penawaran resmi ke pelanggan.
 *
 * Sama seperti kalkulator "Estimasi Hemat" di atasnya: rincian biaya dan
 * grafik penghematan hanya ditampilkan setelah data pelanggan (nama +
 * WhatsApp) diisi valid dan tombol "Dapatkan Hasil & Estimasi" ditekan.
 */

const PANELS = [
    { b: 'Merk A', m: 'MS200M-60', wp: 200, w: 1480, h: 770, rp: 7200, type: 'Mono' },
    { b: 'Merk A', m: 'MS300M-72', wp: 300, w: 1990, h: 800, rp: 6800, type: 'Mono' },
    { b: 'Merk B', m: 'MS330P-72', wp: 330, w: 1990, h: 920, rp: 5900, type: 'Poly' },
    { b: 'Merk A', m: 'MS370MB-60H', wp: 370, w: 1755, h: 1038, rp: 6500, type: 'Mono' },
    { b: 'Merk A', m: 'MS400MB-54H', wp: 400, w: 1722, h: 1134, rp: 6500, type: 'Mono' },
    { b: 'Merk C', m: 'MS435BC-54H', wp: 435, w: 1722, h: 1134, rp: 6900, type: 'Mono' },
    { b: 'Merk C', m: 'MS455MB-72H', wp: 455, w: 2094, h: 1038, rp: 6700, type: 'Mono' },
    { b: 'Merk C', m: 'MS480M-SHLD', wp: 480, w: 2275, h: 1086, rp: 6600, type: 'Mono' },
    { b: 'Merk C', m: 'MS540MB-72H', wp: 540, w: 2279, h: 1134, rp: 6400, type: 'Mono' },
    { b: 'Merk C', m: 'MS580BC-72H', wp: 580, w: 2279, h: 1134, rp: 6300, type: 'Mono' },
];

const INVERTERS = [
    { b: 'SUOER', m: 'FPV 5.5 KVA', kva: 5.5, rp: 3500000, hy: false, v: '-' },
    { b: 'SUOER', m: 'FPV 7.5 KVA', kva: 7.5, rp: 4000000, hy: false, v: '-' },
    { b: 'SUOER', m: 'FPV 10.5 KVA', kva: 10.5, rp: 5000000, hy: false, v: '-' },
    { b: 'SUOER', m: 'FPV 12.5 KVA', kva: 12.5, rp: 9000000, hy: false, v: '-' },
    { b: 'VMS', m: 'Hybrid 3.2 KVA', kva: 3.2, rp: 7500000, hy: true, v: '24V' },
    { b: 'VMS', m: 'Hybrid 4.2 KVA', kva: 4.2, rp: 7800000, hy: true, v: '24V' },
    { b: 'VMS', m: 'Hybrid 5 KVA', kva: 5, rp: 9000000, hy: true, v: '48V' },
    { b: 'VMS', m: 'Hybrid 6.2 KVA', kva: 6.2, rp: 9500000, hy: true, v: '48V' },
    { b: 'VMS', m: 'Hybrid 10.2 KVA', kva: 10.2, rp: 11000000, hy: true, v: '48V' },
];

const BATTS = [
    { b: 'Merk D', m: 'LiFePO4 12.8V 100Ah', kwh: 1.28, rp: 3500000 },
    { b: 'Merk D', m: 'LiFePO4 48V 100Ah', kwh: 4.8, rp: 25000000 },
];

const ROOF = {
    genteng: { label: 'Genteng', desc: 'Perlu kait khusus dan penyesuaian di tiap titik, jadi bracket lebih mahal.', rp: 185000 },
    metal: { label: 'Metal / Spandek', desc: 'Paling mudah dipasang, bracket paling murah.', rp: 150000 },
    dak: { label: 'Dak Beton', desc: 'Butuh rangka dudukan dan pemberat, bracket paling mahal.', rp: 210000 },
};

const KABEL = 2800000 + 120000 + 2700000;
const JASA_WATT = 1500;
const SUN = 4.5;
const PR = 0.78;
const DOD = 0.8;
const YEARS = 25;
const INV_LIFE = 12;
const BAT_LIFE = 10;
const STEP_LABELS = ['Pemakaian', 'Kondisi', 'Atap', 'Panel', 'Inverter', 'Baterai', 'Ringkasan'];

export default function pltsCalculatorComponent() {
    return {
        step: 1,
        maxStep: 1,
        mode: 'pln',
        plnVa: '5500',
        loadFactor: 30,
        dailyKwh: 14,
        appliances: [
            { name: 'Kulkas', w: 150, q: 1, h: 24 },
            { name: 'AC 1 PK', w: 840, q: 2, h: 8 },
            { name: 'Lampu LED', w: 15, q: 12, h: 6 },
            { name: 'TV', w: 120, q: 1, h: 5 },
            { name: 'Pompa air', w: 250, q: 1, h: 2 },
        ],
        dayShare: 60,
        useBattery: false,
        roof: 'genteng',
        roofArea: 40,
        panel: null,
        inverter: null,
        battery: null,
        tariff: 1700,
        inflation: 3,
        degradation: 0.55,
        includeReplacement: true,

        lead: { name: '', phone: '', email: '', area: 'Jakarta Selatan' },
        resultsUnlocked: false,
        submitting: false,
        submitted: false,
        error: null,
        whatsappUrl: null,

        stepLabels: STEP_LABELS,
        roofOptions: ROOF,

        get visibleSteps() {
            return this.useBattery ? [1, 2, 3, 4, 5, 6, 7] : [1, 2, 3, 4, 5, 7];
        },

        goto(n) {
            this.step = n;
            if (n > this.maxStep) this.maxStep = n;
        },

        next() {
            const v = this.visibleSteps;
            const i = v.indexOf(this.step);
            if (i < v.length - 1 && this.canProceed) this.goto(v[i + 1]);
        },

        back() {
            const v = this.visibleSteps;
            const i = v.indexOf(this.step);
            if (i > 0) this.goto(v[i - 1]);
        },

        get canProceed() {
            if (this.step === 4) return !!this.panel;
            if (this.step === 5) return !!this.inverter;
            if (this.step === 6) return !!this.battery;
            return true;
        },

        get isLastStep() {
            const v = this.visibleSteps;
            return v.indexOf(this.step) === v.length - 1;
        },

        get isSecondToLastStep() {
            const v = this.visibleSteps;
            return v.indexOf(this.step) === v.length - 2;
        },

        addAppliance() {
            this.appliances.push({ name: 'Alat baru', w: 100, q: 1, h: 4 });
        },

        removeAppliance(i) {
            this.appliances.splice(i, 1);
        },

        pct(value, min, max) {
            return ((value - min) / (max - min)) * 100;
        },

        get totalDailyKwh() {
            if (this.mode === 'pln') return (Number(this.plnVa) * (this.loadFactor / 100) * 24) / 1000;
            if (this.mode === 'kwh') return Number(this.dailyKwh);

            return this.appliances.reduce((sum, a) => sum + ((a.w || 0) * (a.q || 0) * (a.h || 0)) / 1000, 0);
        },

        get calc() {
            const t = this.totalDailyKwh;
            const share = this.useBattery ? 1 : this.dayShare / 100;
            const cov = t * share;
            const wpNeed = (cov * 1000) / SUN / PR;
            const count = this.panel ? Math.ceil(wpNeed / this.panel.wp) : 0;
            const arrayWp = this.panel ? count * this.panel.wp : 0;
            const m2 = this.panel ? count * ((this.panel.w * this.panel.h) / 1e6) : 0;
            const nightKwh = t * (1 - this.dayShare / 100);
            const batteryUnits = (this.useBattery && this.battery)
                ? Math.max(1, Math.ceil(nightKwh / (this.battery.kwh * DOD)))
                : 0;

            return { t, cov, wpNeed, count, arrayWp, m2, nightKwh, batteryUnits };
        },

        get costs() {
            const c = this.calc;
            const panel = this.panel ? c.arrayWp * this.panel.rp : 0;
            const bracket = c.m2 * ROOF[this.roof].rp;
            const inv = this.inverter ? this.inverter.rp : 0;
            const batt = (this.useBattery && this.battery) ? c.batteryUnits * this.battery.rp : 0;
            const kabel = KABEL;
            const jasa = c.arrayWp * JASA_WATT;

            return { panel, bracket, inv, batt, kabel, jasa, total: panel + bracket + inv + batt + kabel + jasa };
        },

        get savings() {
            const c = this.calc;
            const o = this.costs;
            const tariff = Number(this.tariff);
            const infl = this.inflation / 100;
            const deg = this.degradation / 100;
            const repl = this.includeReplacement;

            const genDay = (c.arrayWp * SUN * PR) / 1000;
            const usedDay = Math.min(genDay, c.cov);
            const wasteDay = Math.max(0, genDay - c.cov);

            const rows = [];
            let cumS = 0;
            let cumI = o.total;
            let breakeven = null;

            for (let y = 1; y <= YEARS; y++) {
                const gen = usedDay * 365 * (1 - deg) ** (y - 1);
                const tr = tariff * (1 + infl) ** (y - 1);
                cumS += gen * tr;

                if (repl) {
                    if (y === INV_LIFE) cumI += o.inv;
                    if (o.batt && y === BAT_LIFE) cumI += o.batt;
                    if (o.batt && y === BAT_LIFE * 2) cumI += o.batt;
                }

                rows.push({ y, s: cumS, i: cumI });

                if (breakeven === null && cumS >= cumI) {
                    const prev = y > 1 ? rows[y - 2] : null;
                    const pv = prev ? prev.s : 0;
                    const pi = prev ? prev.i : o.total;
                    breakeven = pv >= pi ? y - 1 : (y - 1) + (pi - pv) / ((cumS - pv) - (cumI - pi) || 1);
                }
            }

            return {
                rows,
                breakeven,
                y1: rows[0].s,
                total: cumS,
                finalInvestment: cumI,
                genYear: usedDay * 365,
                wasteYear: wasteDay * 365,
            };
        },

        panelRows() {
            const wpNeed = this.calc.wpNeed;
            const avail = this.roofArea;
            const rows = PANELS.map((p) => {
                const count = wpNeed > 0 ? Math.ceil(wpNeed / p.wp) : 0;
                const area = count * ((p.w * p.h) / 1e6);
                const cost = count * p.wp * p.rp;

                return { panel: p, count, area, wp: count * p.wp, cost, off: area > avail };
            }).sort((a, b) => b.panel.wp - a.panel.wp);

            let best = null;
            rows.forEach((r) => {
                if (!r.off && (!best || r.cost < best.cost)) best = r;
            });
            rows.forEach((r) => { r.isBest = r === best; });

            return rows;
        },

        inverterRows() {
            const need = this.calc.arrayWp / 1000;
            const list = INVERTERS.filter((i) => i.hy === this.useBattery);
            const ok = list.filter((i) => i.kva >= need);
            const best = ok.length ? ok[0] : null;

            return list.map((i) => ({ inv: i, need, off: i.kva < need, isBest: i === best }));
        },

        batteryRows() {
            const nightKwh = this.calc.nightKwh;

            return BATTS.map((b) => {
                const units = Math.max(1, Math.ceil(nightKwh / (b.kwh * DOD)));

                return { batt: b, units, cost: units * b.rp };
            });
        },

        chartSvg() {
            const sv = this.savings;
            const W = 620;
            const H = 220;
            const PL = 8;
            const PRm = 8;
            const PT = 14;
            const PB = 26;
            const maxV = Math.max(sv.rows[YEARS - 1].s, sv.finalInvestment) * 1.08 || 1;
            const x = (y) => PL + (y / YEARS) * (W - PL - PRm);
            const yy = (v) => PT + (1 - v / maxV) * (H - PT - PB);

            let sp = `M ${x(0)} ${yy(0)}`;
            let ip = `M ${x(0)} ${yy(sv.rows[0].i)}`;
            let iPrev = null;

            sv.rows.forEach((r, k) => {
                sp += ` L ${x(r.y)} ${yy(r.s)}`;

                if (k === 0) {
                    ip = `M ${x(0)} ${yy(r.i)}`;
                } else if (r.i !== iPrev) {
                    ip += ` L ${x(r.y)} ${yy(iPrev)} L ${x(r.y)} ${yy(r.i)}`;
                }

                ip += ` L ${x(r.y)} ${yy(r.i)}`;
                iPrev = r.i;
            });

            const area = `${sp} L ${x(YEARS)} ${yy(0)} L ${x(0)} ${yy(0)} Z`;

            let grid = '';
            [0.25, 0.5, 0.75, 1].forEach((f) => {
                grid += `<line x1="${PL}" y1="${yy(maxV * f)}" x2="${W - PRm}" y2="${yy(maxV * f)}" stroke="#bec7d3" stroke-width="1" stroke-dasharray="4 4"/>`;
            });

            let labels = '';
            [0, 5, 10, 15, 20, 25].forEach((t) => {
                const anchor = t === 0 ? 'start' : t === 25 ? 'end' : 'middle';
                labels += `<text x="${x(t)}" y="${H - 7}" font-size="10.5" fill="#5f6673" font-family="Inter,sans-serif" text-anchor="${anchor}">Thn ${t}</text>`;
            });

            let breakevenMark = '';
            if (sv.breakeven) {
                const bx = x(sv.breakeven);
                const idx = Math.min(YEARS - 1, Math.round(sv.breakeven) - 1);
                breakevenMark = `<line x1="${bx}" y1="${PT}" x2="${bx}" y2="${H - PB}" stroke="#0099e5" stroke-width="1.5" stroke-dasharray="3 3" opacity=".5"/>`
                    + `<circle cx="${bx}" cy="${yy(sv.rows[idx].s)}" r="4.5" fill="#0099e5" stroke="#fff" stroke-width="2"/>`;
            }

            return `<svg viewBox="0 0 ${W} ${H}" class="w-full h-auto block" role="img" aria-label="Grafik akumulasi penghematan dibanding investasi selama 25 tahun">`
                + '<defs><linearGradient id="plts-fade" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#0099e5" stop-opacity=".16"/><stop offset="100%" stop-color="#0099e5" stop-opacity="0"/></linearGradient></defs>'
                + grid
                + `<path d="${area}" fill="url(#plts-fade)"/>`
                + `<path d="${ip}" fill="none" stroke="#bec7d3" stroke-width="2.5" stroke-linejoin="round"/>`
                + `<path d="${sp}" fill="none" stroke="#0099e5" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>`
                + breakevenMark
                + labels
                + '</svg>';
        },

        buildSummary() {
            const c = this.calc;
            const o = this.costs;
            const sv = this.savings;

            const lines = [
                'Kalkulator Detail Sistem PLTS',
                `Pemakaian harian: ${this.formatNumber(c.t, 1)} kWh`,
                `Dicover panel: ${this.formatNumber(c.cov, 1)} kWh (${this.useBattery ? 'penuh, pakai baterai' : this.dayShare + '% siang'})`,
                `Kapasitas sistem: ${this.formatNumber(c.arrayWp)} Wp`,
                this.panel ? `Panel: ${c.count}x ${this.panel.b} ${this.panel.m} (${this.panel.wp} Wp)` : null,
                `Luas atap terpakai: ${this.formatNumber(c.m2, 1)} m2 dari ${this.formatNumber(this.roofArea)} m2`,
                `Jenis atap: ${this.roofOptions[this.roof].label}`,
                this.inverter ? `Inverter: ${this.inverter.b} ${this.inverter.m}` : null,
                (this.useBattery && this.battery) ? `Baterai: ${c.batteryUnits}x ${this.battery.b} ${this.battery.m}` : null,
                '',
                `Estimasi total investasi: ${this.formatRupiah(o.total)}`,
                `Hemat tahun pertama: ${this.formatRupiah(sv.y1)}`,
                `Total hemat 25 tahun: ${this.formatRupiah(sv.total)}`,
                `Balik modal: ${sv.breakeven ? this.formatNumber(sv.breakeven, 1) + ' tahun' : '> 25 tahun'}`,
                '',
                `Area: ${this.lead.area}`,
                this.lead.email ? `Email: ${this.lead.email}` : null,
            ];

            return lines.filter((l) => l !== null).join('\n');
        },

        unlockResults() {
            this.error = null;

            if (!this.lead.name.trim()) {
                this.error = 'Masukkan nama lengkap Anda.';

                return;
            }

            if (!/^[0-9+\-\s]{8,15}$/.test(this.lead.phone.trim())) {
                this.error = 'Masukkan nomor WhatsApp yang valid.';

                return;
            }

            if (!this.panel || !this.inverter || (this.useBattery && !this.battery)) {
                this.error = 'Lengkapi pemilihan panel/inverter/baterai terlebih dahulu.';

                return;
            }

            this.resultsUnlocked = true;
            this.submitLead();
        },

        async submitLead() {
            this.submitting = true;

            try {
                const res = await fetch('/kalkulator/lead', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        name: this.lead.name,
                        phone: this.lead.phone,
                        email: this.lead.email || null,
                        area: this.lead.area,
                        category: 'residential',
                        summary: this.buildSummary(),
                    }),
                });

                if (res.ok) {
                    this.submitted = true;
                    const body = await res.json();

                    if (body.whatsapp_url) {
                        this.whatsappUrl = body.whatsapp_url;
                    }
                } else if (res.status === 422) {
                    const body = await res.json();
                    this.error = Object.values(body.errors ?? {})[0]?.[0] ?? body.message;
                } else {
                    this.error = 'Gagal mengirim data. Silakan coba lagi.';
                }
            } catch (e) {
                this.error = 'Gagal terhubung ke server. Periksa koneksi Anda.';
            } finally {
                this.submitting = false;
            }
        },

        formatRupiah(value) {
            return 'Rp ' + Math.round(value).toLocaleString('id-ID');
        },

        formatNumber(value, decimals = 0) {
            return Number(value).toLocaleString('id-ID', {
                maximumFractionDigits: decimals,
                minimumFractionDigits: decimals,
            });
        },
    };
}
