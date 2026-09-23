const STORAGE_KEY = 'cookie-consent';

/**
 * Persetujuan cookie pengunjung (spec 023-site-settings, FR-050 sampai
 * FR-058). Pilihan hanya diingat di localStorage perangkat pengunjung
 * sendiri — TIDAK PERNAH dikirim atau dicatat di sisi server (FR-058).
 * Bila localStorage tidak tersedia atau kosong, pengunjung diperlakukan
 * sebagai belum memilih (contracts/consent-gating-contract.md §6).
 */
export default function cookieConsent() {
    return {
        visible: false,
        preferencesOpen: false,
        categories: { analytics: false, marketing: false },

        init() {
            const stored = readStored();

            if (stored) {
                this.categories = { analytics: !!stored.analytics, marketing: !!stored.marketing };
                this.activateApproved();
                this.visible = false;
            } else {
                this.visible = true;
            }

            window.addEventListener('open-cookie-preferences', () => {
                this.preferencesOpen = true;
            });
        },

        acceptAll() {
            this.categories = { analytics: true, marketing: true };
            this.persistAndActivate();
        },

        rejectAll() {
            this.categories = { analytics: false, marketing: false };
            this.persistAndActivate();
        },

        openPreferences() {
            this.preferencesOpen = true;
        },

        savePreferences() {
            this.preferencesOpen = false;
            this.persistAndActivate();
        },

        persistAndActivate() {
            writeStored(this.categories);
            this.activateApproved();
            this.visible = false;
        },

        /**
         * Mengaktifkan slot yang kategorinya baru disetujui, tanpa memuat
         * ulang halaman. Setiap <template data-consent-category> yang
         * kategorinya disetujui dipindahkan keluar sebagai node DOM asli;
         * elemen <script> di dalamnya dibuat ulang lewat createElement
         * supaya benar-benar dieksekusi peramban — memindahkan node
         * <script> apa adanya (mis. lewat innerHTML/appendChild langsung)
         * TIDAK memicu eksekusi di kebanyakan peramban.
         */
        activateApproved() {
            document
                .querySelectorAll('template[data-consent-category]')
                .forEach((template) => {
                    const category = template.dataset.consentCategory;

                    if (!this.categories[category]) {
                        return;
                    }

                    try {
                        const fragment = template.content.cloneNode(true);

                        fragment.querySelectorAll('script').forEach((oldScript) => {
                            const newScript = document.createElement('script');

                            for (const attr of oldScript.attributes) {
                                newScript.setAttribute(attr.name, attr.value);
                            }

                            newScript.textContent = oldScript.textContent;
                            oldScript.replaceWith(newScript);
                        });

                        template.replaceWith(fragment);
                    } catch (error) {
                        // Kegagalan satu slot tidak boleh menghalangi isi
                        // halaman lain tampil dan dinavigasi (FR-049).
                        console.error('Gagal mengaktifkan skrip berkategori '+category, error);
                    }
                });
        },
    };
}

function readStored() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);

        return raw ? JSON.parse(raw) : null;
    } catch (error) {
        return null;
    }
}

function writeStored(categories) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(categories));
    } catch (error) {
        // localStorage tidak tersedia (mis. mode privat) — persetujuan
        // tidak diingat, pengunjung akan ditanya lagi kunjungan berikutnya.
        // Ini konsekuensi yang diterima sadar (FR-058), bukan bug.
    }
}
