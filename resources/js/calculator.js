/**
 * Kalkulator Estimasi Hemat Home page (FR-006). Perhitungan berjalan
 * SEPENUHNYA DI SERVER (App\Services\SavingsEstimator) — komponen ini hanya
 * mengumpulkan input mentah (tagihan/peralatan) dan menggambar ulang hasil +
 * grafik dari response `POST /kalkulator/lead`. Ini disengaja: supaya angka
 * yang ditampilkan di layar selalu identik dengan yang tersimpan di database
 * dan yang dikirim lewat email — tidak ada lagi hitungan duplikat di client
 * yang bisa berbeda dari server.
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
        chartPoints: null, // titik-titik dari server, dipakai untuk menggambar grafik
        whatsappUrl: null,

        get effectiveMethod() {
            return this.category === 'industrial' ? 'bill' : this.method;
        },

        // Grafik digambar dari `chartPoints` (balikan server), bukan dihitung
        // ulang di sini, supaya bentuknya selalu konsisten dengan angka hasil.
        get chartPath() {
            const points = this.scaledChartPoints();

            if (!points.length) {
                return 'M 0 140 Q 100 130, 200 100 T 400 20';
            }

            let d = `M ${points[0].x} ${points[0].y}`;

            for (let i = 0; i < points.length - 1; i++) {
                const p0 = points[i];
                const p1 = points[i + 1];
                const midX = (p0.x + p1.x) / 2;
                const midY = (p0.y + p1.y) / 2;
                d += ` Q ${p0.x} ${p0.y}, ${midX} ${midY}`;
            }

            const last = points[points.length - 1];
            d += ` L ${last.x} ${last.y}`;

            return d;
        },

        get chartFillPath() {
            return `${this.chartPath} L 400 150 L 0 150 Z`;
        },

        // Konversi titik {year, cumulative_savings} dari server ke koordinat
        // SVG (viewBox 0 0 400 150), diberi titik awal tahun-0 di pojok kiri
        // bawah supaya kurva mulai dari nol.
        scaledChartPoints() {
            if (!this.chartPoints || !this.chartPoints.length) {
                return [];
            }

            const investment = this.result?.estimatedInvestment || 1;
            const maxYear = this.chartPoints[this.chartPoints.length - 1].year;
            const toXY = ({ year, cumulative_savings: cumulative }) => {
                const ratio = Math.min(cumulative / (investment * 3), 1);

                return {
                    x: Math.round((year / maxYear) * 400),
                    y: Math.round(140 - ratio * 120),
                };
            };

            return [{ year: 0, cumulative_savings: 0 }, ...this.chartPoints].map(toXY);
        },

        resetResult() {
            this.result = null;
            this.chartPoints = null;
            this.error = null;
            this.submitted = false;
        },

        buildAppliancePayload() {
            return this.appliances
                .filter((item) => item.qty > 0)
                .map((item) => ({ key: item.key, qty: item.qty }));
        },

        readUtmParams() {
            const params = new URLSearchParams(window.location.search);
            const utm = {};

            ['utm_source', 'utm_medium', 'utm_campaign'].forEach((k) => {
                if (params.get(k)) {
                    utm[k.replace('utm_', '')] = params.get(k);
                }
            });

            return Object.keys(utm).length ? utm : null;
        },

        async calculate() {
            this.error = null;
            this.result = null;
            this.chartPoints = null;
            this.submitted = false;

            if (!this.lead.name.trim()) {
                this.error = 'Masukkan nama lengkap Anda.';

                return;
            }

            if (!/^[0-9+\-\s]{8,15}$/.test(this.lead.phone.trim())) {
                this.error = 'Masukkan nomor WhatsApp yang valid.';

                return;
            }

            const method = this.effectiveMethod;

            if (method === 'bill' && this.parseRupiah(this.billInput) <= 0) {
                this.error = 'Masukkan tagihan listrik bulanan yang valid (lebih dari 0).';

                return;
            }

            if (method === 'appliance' && this.buildAppliancePayload().length === 0) {
                this.error = 'Pilih minimal satu peralatan dengan jumlah lebih dari 0.';

                return;
            }

            await this.submitLead(method);
        },

        async submitLead(method) {
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
                        method,
                        monthly_bill: method === 'bill' ? this.parseRupiah(this.billInput) : null,
                        va_capacity: this.category === 'residential' ? this.vaCapacity : null,
                        appliances: method === 'appliance' ? this.buildAppliancePayload() : null,
                        utm: this.readUtmParams(),
                    }),
                });

                const body = await res.json();

                if (res.ok) {
                    this.result = {
                        savingsYear1: body.result.savings_year1,
                        totalSavings25Years: body.result.total_savings_25y,
                        estimatedInvestment: body.result.estimated_investment,
                        breakevenYears: Math.round(body.result.breakeven_years),
                        annualKwh: body.result.annual_kwh,
                    };
                    this.chartPoints = body.chart.points;
                    this.whatsappUrl = body.whatsapp_url ?? null;
                    this.submitted = true;
                } else if (res.status === 422) {
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
