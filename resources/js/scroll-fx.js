/**
 * Efek scroll global untuk halaman publik (port dari mockup-master):
 *  1. Nav shrink/opaque saat halaman di-scroll (dipakai <x-layout.header> via
 *     event `suoer:scrolled`).
 *  2. Reveal-on-scroll: elemen ber-class `.reveal-element` di-fade-in saat
 *     masuk viewport (IntersectionObserver). Menghormati prefers-reduced-motion.
 */
export default function initScrollFx() {
    const revealEls = document.querySelectorAll('.reveal-element');

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        revealEls.forEach((el) => el.classList.add('is-visible'));

        return;
    }

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        obs.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
        );

        revealEls.forEach((el) => observer.observe(el));
    } else {
        revealEls.forEach((el) => el.classList.add('is-visible'));
    }
}
