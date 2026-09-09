/**
 * Reveal-on-scroll global untuk halaman publik (port dari mockup-master).
 * Elemen ber-class `.reveal-element` di-fade-in saat mendekati viewport.
 *
 * Progressive enhancement: state tersembunyi hanya berlaku bila JS aktif
 * (class `js` di <html>, di-set inline di <head>). Tanpa JS seluruh konten
 * tetap terlihat. Ada beberapa jaring pengaman supaya konten TIDAK PERNAH
 * tersangkut tak terlihat: observer memicu jauh sebelum elemen masuk layar,
 * dan sebuah timeout + event `load` memaksa semua elemen terlihat.
 */
export default function initScrollFx() {
    const revealEls = Array.from(document.querySelectorAll('.reveal-element'));

    const revealAll = () => revealEls.forEach((el) => el.classList.add('is-visible'));

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        revealAll();

        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        },
        // rootMargin besar: elemen "terlihat" 600px sebelum benar-benar masuk
        // layar, jadi animasi selesai sebelum user sampai ke sana dan tidak ada
        // konten yang tampak kosong saat scroll cepat.
        { threshold: 0, rootMargin: '600px 0px 600px 0px' },
    );

    revealEls.forEach((el) => observer.observe(el));

    // Jaring pengaman keras: apa pun yang terjadi, semua konten terlihat
    // setelah window `load` + 600ms.
    window.addEventListener('load', () => window.setTimeout(revealAll, 600));
    window.setTimeout(revealAll, 3000);
}
