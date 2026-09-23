@props(['message' => null])

{{--
    Bilah persetujuan cookie non-blokir (FR-073, FR-074) — pengunjung tetap
    bisa memakai seluruh halaman selama belum memutuskan. Logika penyimpanan
    dan aktivasi skrip ada di resources/js/cookie-consent.js (Alpine.data
    'cookieConsent'), disimpan di localStorage pengunjung sendiri, tidak
    pernah di sisi server (FR-058).
--}}
<div
    x-data="cookieConsent()"
    x-cloak
    data-cookie-consent-banner
>
    <div
        x-show="visible"
        x-transition
        class="fixed inset-x-0 bottom-0 z-[60] bg-on-background text-white px-6 py-5 shadow-2xl"
        role="region"
        aria-label="Persetujuan cookie"
    >
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <p class="text-sm text-white/80 leading-relaxed">
                {{ $message ?: 'Kami menggunakan cookie untuk meningkatkan pengalaman Anda dan menganalisis penggunaan situs. Anda dapat menerima, menolak, atau mengatur pilihan per kategori.' }}
                <button type="button" @click="openPreferences()" class="underline hover:text-white ml-1">Atur preferensi</button>
            </p>
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" @click="rejectAll()" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-white/30 text-white hover:bg-white/10 transition-colors">
                    Tolak
                </button>
                <button type="button" @click="acceptAll()" class="px-5 py-2.5 rounded-lg text-sm font-medium bg-primary-container text-white hover:bg-primary-container/90 transition-colors">
                    Terima
                </button>
            </div>
        </div>
    </div>

    <div
        x-show="preferencesOpen"
        x-transition
        class="fixed inset-0 z-[70] bg-black/50 flex items-center justify-center p-6"
        role="dialog"
        aria-modal="true"
        aria-label="Pengaturan cookie"
    >
        <div @click.outside="preferencesOpen = false" class="bg-white rounded-lg shadow-2xl max-w-md w-full p-6 space-y-5">
            <h2 class="font-headline-lg text-lg font-bold text-on-background">Pengaturan Cookie</h2>

            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="font-medium text-on-background text-sm">Diperlukan Agar Situs Berfungsi</p>
                    <p class="text-xs text-on-surface-variant">Selalu aktif, tidak dapat dimatikan.</p>
                </div>
                <input type="checkbox" checked disabled class="w-5 h-5">
            </div>

            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="font-medium text-on-background text-sm">Analitik</p>
                    <p class="text-xs text-on-surface-variant">Membantu kami memahami penggunaan situs.</p>
                </div>
                <input type="checkbox" x-model="categories.analytics" class="w-5 h-5">
            </div>

            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="font-medium text-on-background text-sm">Pemasaran</p>
                    <p class="text-xs text-on-surface-variant">Dipakai untuk iklan yang relevan.</p>
                </div>
                <input type="checkbox" x-model="categories.marketing" class="w-5 h-5">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="preferencesOpen = false" class="px-4 py-2 text-sm font-medium text-on-surface-variant hover:text-on-background transition-colors">
                    Batal
                </button>
                <button type="button" @click="savePreferences()" class="px-5 py-2.5 rounded-lg text-sm font-medium bg-primary-container text-white hover:bg-primary-container/90 transition-colors">
                    Simpan Preferensi
                </button>
            </div>
        </div>
    </div>
</div>
