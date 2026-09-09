/**
 * Kalkulator Estimasi Hemat Home page (FR-006). Perhitungan berjalan di sisi
 * client; setelah "Dapatkan Hasil Analisis" ditekan, data lead (nama/WA/email/
 * area + ringkasan estimasi) dikirim ke `POST /kalkulator/lead` untuk disimpan
 * sebagai ContactSubmission agar tim sales bisa menindaklanjuti.
 *
 * Estimasi memakai asumsi sederhana (bukan perhitungan teknik presisi):
 * - Tarif listrik rata-rata Rp 1.500/kWh
 * - Panel surya menutup ~70% konsumsi bulanan
 * - Investasi awal diasumsikan proporsional terhadap tagihan/pemakaian
 * - Eskalasi tarif listrik 3%/tahun dipakai untuk proyeksi 25 tahun
 */
export default function calculatorComponent() {
    return {
        category: 'residential', // 'residential' | 'industrial'
        method: 'bill', // 'bill' | 'appliance' (hanya residential)
        billInput: '',
        vaCapacity: '2200',
        appliances: [
            { key: 'tv', label: 'TV', icon: 'tv', watt: 100, qty: 0 },
            { key: 'kulkas', label: 'Kulkas', icon: 'kitchen', watt: 200, qty: 0 },
            { key: 'ac', label: 'AC', icon: 'ac_unit', watt: 1000, qty: 0 },
            { key: 'pompa', label: 'Pompa Air', icon: 'water_drop', watt: 250, qty: 0 },
            { key: 'pemanas', label: 'Pemanas Air', icon: 'hot_tub', watt: 1500, qty: 0 },
            { key: 'kompor', label: 'Kompor Listrik', icon: 'cooking', watt: 2000, qty: 0 },
        ],
        lead: { name: '', phone: '', email: '', area: 'Jakarta Selatan' },
        error: null,
        submitting: false,
        submitted: false,
        result: null,

        get effectiveMethod() {
            return this.category === 'industrial' ? 'bill' : this.method;
        },

        get chartPath() {
            if (!this.result) {
                return 'M 0 140 Q 100 130, 200 100 T 400 20';
            }

            const peak = 130 - Math.min(this.result.progressRatio * 110, 110);

            return `M 0 140 Q 100 ${Math.round(140 - this.result.progressRatio * 20)}, 200 ${Math.round(140 - this.result.progressRatio * 60)} T 400 ${Math.round(peak)}`;
        },

        get chartFillPath() {
            return `${this.chartPath} L 400 150 L 0 150 Z`;
        },

        resetResult() {
            this.result = null;
            this.error = null;
            this.submitted = false;
        },

        compute() {
            const TARIFF_PER_KWH = 1500;
            const SOLAR_COVERAGE = 0.7;
            const ESCALATION = 1.03;

            let monthlyBill;

            if (this.effectiveMethod === 'bill') {
                const bill = this.parseRupiah(this.billInput);

                if (!bill || bill <= 0) {
                    this.error = 'Masukkan tagihan listrik bulanan yang valid (lebih dari 0).';

                    return null;
                }

                monthlyBill = bill;
            } else {
                const totalWatt = this.appliances.reduce((sum, item) => sum + item.watt * item.qty, 0);

                if (totalWatt <= 0) {
                    this.error = 'Pilih minimal satu peralatan dengan jumlah lebih dari 0.';

                    return null;
                }

                const monthlyKwh = (totalWatt / 1000) * 6 * 30;
                monthlyBill = monthlyKwh * TARIFF_PER_KWH;
            }

            const annualSavingsYear1 = monthlyBill * 12 * SOLAR_COVERAGE;
            const estimatedInvestment = annualSavingsYear1 * 6.8;

            let cumulative = 0;
            let breakevenYear = null;

            for (let year = 1; year <= 25; year++) {
                cumulative += annualSavingsYear1 * ESCALATION ** (year - 1);

                if (breakevenYear === null && cumulative >= estimatedInvestment) {
                    breakevenYear = year;
                }
            }

            const annualKwh = (monthlyBill / TARIFF_PER_KWH) * 12 * SOLAR_COVERAGE;

            return {
                savingsYear1: Math.round(annualSavingsYear1),
                totalSavings25Years: Math.round(cumulative),
                breakevenYears: breakevenYear ?? 25,
                annualKwh: Math.round(annualKwh * 10) / 10,
                progressRatio: Math.min(cumulative / (estimatedInvestment * 3), 1),
            };
        },

        buildSummary() {
            const r = this.result;
            const lines = [
                `Kategori: ${this.category === 'industrial' ? 'Industrial / Komersial' : 'Residential'}`,
                `Metode: ${this.effectiveMethod === 'bill' ? 'Berdasarkan Tagihan' : 'Berdasarkan Peralatan'}`,
            ];

            if (this.effectiveMethod === 'bill') {
                lines.push(`Tagihan bulanan: Rp ${this.parseRupiah(this.billInput).toLocaleString('id-ID')}`);
                if (this.category === 'residential') {
                    lines.push(`Kapasitas PLN: ${this.vaCapacity} VA`);
                }
            } else {
                const picked = this.appliances.filter((a) => a.qty > 0).map((a) => `${a.label} x${a.qty}`);
                lines.push(`Peralatan: ${picked.join(', ')}`);
            }

            lines.push(
                '',
                `Estimasi hemat tahun 1: ${this.formatRupiah(r.savingsYear1)}`,
                `Total hemat 25 tahun: ${this.formatRupiah(r.totalSavings25Years)}`,
                `Breakeven: ${r.breakevenYears} tahun`,
                `Produksi surya tahunan: ${r.annualKwh} kWh`,
                '',
                `Area: ${this.lead.area}`,
                this.lead.email ? `Email: ${this.lead.email}` : null,
            );

            return lines.filter((l) => l !== null).join('\n');
        },

        async calculate() {
            this.error = null;
            this.result = null;
            this.submitted = false;

            if (!this.lead.name.trim()) {
                this.error = 'Masukkan nama lengkap Anda.';

                return;
            }

            if (!/^[0-9+\-\s]{8,15}$/.test(this.lead.phone.trim())) {
                this.error = 'Masukkan nomor WhatsApp yang valid.';

                return;
            }

            const computed = this.compute();

            if (!computed) {
                return;
            }

            this.result = computed;

            await this.submitLead();
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
                        category: this.category,
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

        parseRupiah(value) {
            const cleaned = String(value).replace(/[^0-9]/g, '');

            return cleaned ? parseInt(cleaned, 10) : 0;
        },

        formatRupiah(value) {
            return 'Rp ' + Math.round(value).toLocaleString('id-ID');
        },
    };
}
