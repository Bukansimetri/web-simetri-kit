/**
 * Slider hero manual, TANPA auto-rotate (FR-011, research.md R6). Mengikuti
 * pola registrasi Alpine.data() seperti `calculatorComponent`, bukan x-data
 * inline panjang yang dipakai komponen carousel lama.
 *
 * Navigasi: panah kiri/kanan (melingkar), titik langsung, papan ketik
 * (ArrowLeft/ArrowRight saat fokus di dalam slider), dan geser sentuh
 * mendatar (geser menegak diabaikan supaya scroll halaman tetap normal).
 *
 * Penanda "bisa diklik": animasi nudge CSS pada panah, dipicu sekali saat
 * slider memasuki viewport lewat IntersectionObserver, lalu berhenti
 * sendiri (research.md R7). Dimatikan sepenuhnya bila pengguna meminta
 * pengurangan gerak.
 */
export default function heroSlider(count = 1) {
    return {
        active: 0,
        count,
        hasInteracted: false,
        touchStartX: null,
        touchStartY: null,
        observer: null,

        init(el) {
            el.addEventListener('keydown', (event) => this.handleKeydown(event));
            el.addEventListener('touchstart', (event) => this.handleTouchStart(event), { passive: true });
            el.addEventListener('touchend', (event) => this.handleTouchEnd(event), { passive: true });
            this.observeEntrance(el);
        },

        next() {
            this.active = (this.active + 1) % this.count;
        },

        prev() {
            this.active = (this.active + this.count - 1) % this.count;
        },

        go(index) {
            this.active = index;
        },

        handleKeydown(event) {
            if (event.key === 'ArrowRight') {
                this.next();
                this.hasInteracted = true;
            } else if (event.key === 'ArrowLeft') {
                this.prev();
                this.hasInteracted = true;
            }
        },

        handleTouchStart(event) {
            const touch = event.touches[0];
            this.touchStartX = touch.clientX;
            this.touchStartY = touch.clientY;
        },

        handleTouchEnd(event) {
            if (this.touchStartX === null || this.touchStartY === null) {
                return;
            }

            const touch = event.changedTouches[0];
            const deltaX = touch.clientX - this.touchStartX;
            const deltaY = touch.clientY - this.touchStartY;

            this.touchStartX = null;
            this.touchStartY = null;

            // Geser menegak (scroll halaman) diabaikan sepenuhnya -- hanya
            // geser mendatar yang cukup jauh yang memindahkan slide.
            if (Math.abs(deltaX) < 40 || Math.abs(deltaX) < Math.abs(deltaY)) {
                return;
            }

            this.hasInteracted = true;

            if (deltaX < 0) {
                this.next();
            } else {
                this.prev();
            }
        },

        prefersReducedMotion() {
            return typeof window.matchMedia === 'function'
                && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        },

        observeEntrance(el) {
            if (typeof IntersectionObserver === 'undefined' || this.prefersReducedMotion()) {
                return;
            }

            this.observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    el.classList.add('hero-slider-nudge');
                    this.observer?.disconnect();

                    // Buang class setelah animasi selesai (~3 siklus) supaya
                    // tidak pernah terulang (FR-012).
                    setTimeout(() => el.classList.remove('hero-slider-nudge'), 3000);
                });
            }, { threshold: 0.4 });

            this.observer.observe(el);
        },
    };
}
