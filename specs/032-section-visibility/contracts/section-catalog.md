# Contract: Katalog Section yang Bisa Disembunyikan

Urutan kelompok dan baris di halaman Tampilan Section mengikuti urutan di bawah.

| Kunci | Halaman | Label | Lokasi di view | Menu isi |
|---|---|---|---|---|
| `beranda.mengapa-beralih` | Beranda | Mengapa Beralih | `home` → `x-sections.why-choose` | Beranda → Mengapa Beralih |
| `beranda.cara-kerja` | Beranda | Cara Kerja (Sederhana dan Mulus) | `home` → `x-sections.how-it-works` | Beranda → Cara Kerja |
| `beranda.solusi` | Beranda | Solusi Untuk Setiap Kebutuhan | `home` → blok Produk Kami | Katalog → Produk |
| `beranda.testimoni` | Beranda | Testimoni (Partner Kami) | `home` → blok Testimoni | Tentang Kami → Testimoni |
| `beranda.cta` | Beranda | CTA Penutup | `home` → blok CTA Penutup | Konten Halaman → CTA |
| `tentang-kami.siapa-kami` | Tentang Kami | Siapa Kami | `tentang-kami` → Siapa Kami | Konten Halaman → Blok Halaman |
| `tentang-kami.visi` | Tentang Kami | Visi | `tentang-kami` → Visi | Konten Halaman → Blok Halaman |
| `tentang-kami.misi` | Tentang Kami | Misi | `tentang-kami` → Misi | Tentang Kami → Misi |
| `tentang-kami.nilai` | Tentang Kami | Nilai | `tentang-kami` → Nilai | Tentang Kami → Nilai |
| `tentang-kami.trust-strip` | Tentang Kami | Trust Strip | `tentang-kami` → Trust Strip | Tentang Kami → Trust Strip |
| `tentang-kami.tim` | Tentang Kami | Tim | `x-sections.team-members` | Tentang Kami → Tim |
| `tentang-kami.testimoni` | Tentang Kami | Testimoni (Partner Kami) | `x-sections.testimonials` | Tentang Kami → Testimoni |
| `tentang-kami.logo-klien` | Tentang Kami | Logo Klien (Dipercaya Oleh) | `x-sections.client-logos` | Tentang Kami → Logo Klien |
| `tentang-kami.cta` | Tentang Kami | CTA | `x-sections.cta-band` (About) | Konten Halaman → CTA |
| `karir.mengapa-bergabung` | Karir | Mengapa Bergabung | `karir` → Values | Karir → Mengapa Bergabung |
| `karir.proses-rekrutmen` | Karir | Proses Rekrutmen | `karir` → Recruitment Process | Karir → Proses Rekrutmen |
| `karir.cta` | Karir | CTA | `x-sections.cta-band` (Career) | Konten Halaman → CTA |
| `produk.cta-kalkulator` | Produk | CTA Kalkulator | `produk/index` → CTA Kalkulator | Konten Halaman → CTA |
| `produk.faq` | Produk | FAQ Seputar Produk | `produk/index` → `x-sections.faq-list` | Konten Halaman → FAQ (Halaman Produk) |
| `produk.cta-penutup` | Produk | CTA Penutup | `produk/index` → CTA Penutup | Konten Halaman → CTA |
| `produk-detail.cta` | Detail Produk | CTA | `produk/show` → CTA | Konten Halaman → CTA |
| `artikel.cta` | Artikel | CTA | `artikel/index` → CTA | Konten Halaman → CTA |
| `artikel-detail.cta` | Detail Artikel | CTA | `x-sections.cta-band` (ArticleDetail) | Konten Halaman → CTA |
| `faq.cta` | FAQ | CTA | `x-sections.cta-band` (Faq) | Konten Halaman → CTA |
| `kontak.faq` | Kontak | FAQ Seputar Konsultasi | `kontak` → `x-sections.faq-list` | Konten Halaman → FAQ (Halaman Kontak) |

Pemetaan CTA (`CtaPlacement` → kunci): Home → `beranda.cta`, ProductCalculator → `produk.cta-kalkulator`, ProductClosing → `produk.cta-penutup`, ProductDetail → `produk-detail.cta`, ArticleIndex → `artikel.cta`, ArticleDetail → `artikel-detail.cta`, About → `tentang-kami.cta`, Faq → `faq.cta`, Career → `karir.cta`.

Di luar katalog (FR-003): hero/banner (termasuk tombol hero dan slider Beranda), tombol "Konsultasi Gratis" di header, daftar utama, formulir kontak, kalkulator Hitung Estimasi, Kalkulator Detail Sistem PLTS.
