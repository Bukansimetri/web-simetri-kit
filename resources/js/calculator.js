/**
 * Kalkulator Estimasi Hemat Home page (FR-006) — berjalan sepenuhnya di sisi
 * client, tanpa request ke server. Dua mode: "Per Tagihan" (input tagihan
 * listrik bulanan) dan "Per Alat" (checklist peralatan rumah tangga).
 *
 * Estimasi memakai asumsi sederhana (bukan perhitungan teknik presisi):
 * - Tarif listrik rata-rata Rp 1.500/kWh
 * - Panel surya menutup ~70% konsumsi bulanan
 * - Investasi awal diasumsikan proporsional terhadap tagihan/pemakaian
 * - Eskalasi tarif listrik 3%/tahun dipakai untuk proyeksi 25 tahun
 */
export default function calculatorComponent() {
    return {
        mode: 'bill', // 'bill' | 'appliance'
        billInput: '',
        appliances: [
            { key: 'tv', label: 'TV', icon: 'tv', watt: 100, qty: 0 },
            { key: 'kulkas', label: 'Kulkas', icon: 'kitchen', watt: 200, qty: 0 },
            { key: 'ac', label: 'AC', icon: 'ac_unit', watt: 1000, qty: 0 },
            { key: 'pompa', label: 'Pompa Air', icon: 'water_drop', watt: 250, qty: 0 },
            { key: 'pemanas', label: 'Pemanas Air', icon: 'hot_tub', watt: 1500, qty: 0 },
            { key: 'kompor', label: 'Kompor Listrik', icon: 'cooking', watt: 2000, qty: 0 },
        ],
        error: null,
        result: null,

        get chartPath() {
            if (!this.result) {
                return 'M 0 140 Q 100 130, 200 100 T 400 20';
            }

            // Kurva sederhana dari 0 ke total savings 25 tahun, dinormalisasi ke viewBox 400x150.
            const peak = 130 - Math.min(this.result.progressRatio * 110, 110);

            return `M 0 140 Q 100 ${Math.round(140 - this.result.progressRatio * 20)}, 200 ${Math.round(140 - this.result.progressRatio * 60)} T 400 ${Math.round(peak)}`;
        },

        get chartFillPath() {
            return `${this.chartPath} L 400 150 L 0 150 Z`;
        },

        calculate() {
            this.error = null;
            this.result = null;

            const TARIFF_PER_KWH = 1500;
            const SOLAR_COVERAGE = 0.7;
            const ESCALATION = 1.03;

            let monthlyBill;

            if (this.mode === 'bill') {
                const bill = this.parseRupiah(this.billInput);

                if (!bill || bill <= 0) {
                    this.error = 'Masukkan tagihan listrik bulanan yang valid (lebih dari 0).';

                    return;
                }

                monthlyBill = bill;
            } else {
                const totalWatt = this.appliances.reduce((sum, item) => sum + item.watt * item.qty, 0);

                if (totalWatt <= 0) {
                    this.error = 'Pilih minimal satu peralatan dengan jumlah lebih dari 0.';

                    return;
                }

                // Asumsi pemakaian rata-rata 6 jam/hari, 30 hari/bulan.
                const monthlyKwh = (totalWatt / 1000) * 6 * 30;
                monthlyBill = monthlyKwh * TARIFF_PER_KWH;
            }

            const annualSavingsYear1 = monthlyBill * 12 * SOLAR_COVERAGE;
            const estimatedInvestment = annualSavingsYear1 * 6.8; // asumsi breakeven awal ~6.8 tahun

            let totalSavings25Years = 0;
            let cumulative = 0;
            let breakevenYear = null;

            for (let year = 1; year <= 25; year++) {
                cumulative += annualSavingsYear1 * ESCALATION ** (year - 1);

                if (breakevenYear === null && cumulative >= estimatedInvestment) {
                    breakevenYear = year;
                }
            }
            totalSavings25Years = cumulative;

            const annualKwh = (monthlyBill / TARIFF_PER_KWH) * 12 * SOLAR_COVERAGE;

            this.result = {
                savingsYear1: Math.round(annualSavingsYear1),
                totalSavings25Years: Math.round(totalSavings25Years),
                breakevenYears: breakevenYear ?? 25,
                annualKwh: Math.round(annualKwh * 10) / 10,
                progressRatio: Math.min(totalSavings25Years / (estimatedInvestment * 3), 1),
            };
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
