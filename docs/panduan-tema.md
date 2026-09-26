# Panduan Tema

**Terakhir diperbarui**: 2026-09-26

Panduan ini untuk developer yang menyiapkan instalasi untuk klien baru dengan identitas visual sendiri. Kebanyakan kebutuhan cukup diatur admin lewat panel tanpa mengubah kode; bagian [Perluasan lewat kode](#perluasan-lewat-kode) hanya untuk kasus di luar pengaturan standar. Untuk menambah section, lihat [panduan section](panduan-section.md). Gambaran project ada di [arsitektur project](arsitektur.md).

## Tanpa kode (panel admin)

Di panel admin buka **Pengaturan Situs → Tampilan** (`app/Filament/Pages/AppearanceSettingsPage.php`). Semua yang diatur di sini langsung berlaku di seluruh halaman publik tanpa deploy ulang:

| Field | Efek |
|---|---|
| Logo | Logo di header dan footer |
| Favicon | Ikon tab browser (termasuk halaman pemeliharaan) |
| Warna Primer | Token `primary` (judul, tombol, aksen utama) |
| Warna Sekunder | Token `secondary` (teks pendukung, aksen kedua) |
| Font Heading | Font judul (token `font-headline-*`) |
| Font Body | Font isi teks (token `font-body-*`) |

Font hanya bisa dipilih dari daftar kurasi (Manrope, Be Vietnam Pro, Inter, Poppins, Plus Jakarta Sans, Nunito Sans, Work Sans, Lato); admin tidak bisa mengetik nama font bebas. Jika sebuah field dikosongkan, halaman publik memakai nilai default desain "Luminous Azure".

Instalasi klien baru biasanya cukup mengisi logo, favicon, dan dua warna. Ubah kode hanya bila klien butuh font di luar daftar, default berbeda untuk semua instalasi baru, atau token tema tambahan.

## Alur token

Nilai dari admin sampai ke tampilan lewat empat langkah:

1. **Simpan**: halaman Tampilan menyimpan nilai ke `app/Settings/AppearanceSettings.php` (grup Spatie Settings `appearance`).
2. **Tulis CSS variable**: `resources/views/layouts/partials/theme-vars.blade.php` menulis `--brand-color-primary`, `--brand-color-secondary`, `--brand-font-heading`, dan `--brand-font-body` ke `:root` pada setiap request, dengan fallback ke konstanta `DEFAULT_*` di `AppearanceSettings` saat nilainya kosong. Partial ini di-include oleh `resources/views/layouts/public.blade.php`.
3. **Petakan ke token Tailwind**: blok `@theme` di `resources/css/app.css` menghubungkan variable itu ke token, misal `--color-primary: var(--brand-color-primary)` dan `--font-headline-lg: var(--brand-font-heading), ...`.
4. **Dipakai section**: class seperti `text-primary`, `bg-secondary`, `font-headline-xl`, dan `font-body-md` di section otomatis mengikuti nilai admin.

Jangan menulis kode warna hex atau nama font langsung di Blade; section akan berhenti mengikuti pengaturan.

Hanya warna primer, warna sekunder, dan dua font yang bersumber dari pengaturan admin. Token turunan lain di `resources/css/app.css` (permukaan, `tertiary`, `on-*`, varian `primary-container`, dst.) bernilai tetap dan tidak diekspos ke admin. Artinya mengubah warna primer tidak otomatis mengubah token turunannya; jika desain klien membutuhkan itu, ubah token turunan di file yang sama atau tambah token baru (lihat di bawah).

## Perluasan lewat kode

### Menambah font ke daftar pilihan

Font harus didaftarkan di **dua tempat sekaligus**:

1. Tambahkan nama font ke konstanta `FONT_OPTIONS` di `app/Settings/AppearanceSettings.php`. Ini membuat font muncul di dropdown admin dan lolos validasi.
2. Tambahkan entri `bunny('Nama Font', { weights: [...] })` di `vite.config.js` (ikuti entri font yang sudah ada; sertakan bobot yang dipakai desain, misalnya 400 dan 700). Ini membuat file font ikut di-bundle saat build.

Lalu jalankan `npm run build`.

> **Jebakan**: jika hanya langkah 1 yang dilakukan, font muncul di dropdown dan bisa dipilih, tetapi tidak pernah dimuat sehingga halaman publik diam-diam memakai font cadangan. Jika hanya langkah 2, font dimuat tetapi admin tidak bisa memilihnya.

Cara memeriksa: pilih font baru di halaman Tampilan, simpan, buka halaman publik, lalu di DevTools cek font yang dipakai pada judul (tab Computed, `font-family`) dan pastikan file font termuat di tab Network. Tambahkan juga font baru ke test di `tests/Feature/Settings/AppearanceSettingsTest.php` bila perlu, misalnya kasus menyimpan font baru.

### Mengubah nilai default tema

Default dipakai saat admin belum mengisi apa pun. Nilainya ada di dua tempat yang harus disamakan:

- Konstanta `DEFAULT_PRIMARY_COLOR`, `DEFAULT_SECONDARY_COLOR`, `DEFAULT_FONT_HEADING`, dan `DEFAULT_FONT_BODY` di `app/Settings/AppearanceSettings.php`.
- Blok `:root` di bagian atas `resources/css/app.css` (fallback jika partial tema tidak dimuat).

Nilai font default harus tercantum di `FONT_OPTIONS`. Jalankan `npm run build` dan `php artisan test --compact tests/Feature/Settings/AppearanceSettingsTest.php` setelah mengubahnya.

Untuk instalasi yang sudah berjalan, default tidak menimpa nilai yang sudah disimpan admin; hanya berlaku untuk field yang masih kosong.

### Menambah token tema baru

Contoh: warna aksen tambahan `accent_color` yang bisa diatur admin dan dipakai section lewat class `text-accent` dan `bg-accent`. Semua path dan kode di bawah adalah contoh (file migrasinya baru dibuat oleh perintah):

**1. Migrasi nilai** (`php artisan make:settings-migration AddAccentColorToAppearanceSettings`, isi `up()`):

```php
$this->migrator->add('appearance.accent_color', null);
```

**2. Properti dan default** di `app/Settings/AppearanceSettings.php`:

```php
public const DEFAULT_ACCENT_COLOR = '#f59e0b';

public ?string $accent_color;
```

**3. Field admin** di `app/Filament/Pages/AppearanceSettingsPage.php`: tambah `ColorPicker::make('accent_color')->label('Warna Aksen')` di section "Warna & Font", isi di `mount()` (`$settings->accent_color ?: AppearanceSettings::DEFAULT_ACCENT_COLOR`), dan simpan di `save()`:

```php
$settings->accent_color = $data['accent_color'] ?? null;
```

**4. CSS variable** di `resources/views/layouts/partials/theme-vars.blade.php`, di dalam blok `:root`:

```blade
--brand-color-accent: {{ $appearance->accent_color ?: \App\Settings\AppearanceSettings::DEFAULT_ACCENT_COLOR }};
```

**5. Token Tailwind** di `resources/css/app.css`: tambah fallback di blok `:root` atas dan pemetaan di blok `@theme`:

```css
:root {
    --brand-color-accent: #f59e0b;
}

@theme {
    --color-accent: var(--brand-color-accent);
}
```

**6. Test** di `tests/Feature/Settings/AppearanceSettingsTest.php`, meniru test warna sekunder yang sudah ada: default terisi saat belum dikonfigurasi, tersimpan dari halaman admin, dan kembali ke default saat dikosongkan.

**7. Build dan cek**: `npm run build`, ubah warna aksen di admin, lalu pastikan elemen yang memakai `text-accent` berubah di halaman publik.

## Batasan saat ini

- **Belum ada live preview tema** (AMC-222, ditunda). Admin harus menyimpan lalu membuka halaman publik untuk melihat hasilnya.
- **Belum ada pemilih varian section di admin** (AMC-221, ditunda); lihat [panduan section](panduan-section.md).
- Font hanya dari daftar kurasi; input nama atau URL font bebas sengaja tidak disediakan.
- Token turunan (permukaan, `tertiary`, `on-*`) tidak diekspos ke admin.
- Kontras warna terhadap teks tidak divalidasi otomatis; periksa manual untuk warna yang dipilih klien.

## Checklist selesai

- [ ] Default di `AppearanceSettings` dan fallback `:root` di `resources/css/app.css` sama.
- [ ] Font baru terdaftar di `FONT_OPTIONS` **dan** `vite.config.js`.
- [ ] `npm run build` sudah dijalankan dan tampilan diperiksa di halaman publik.
- [ ] Token baru punya default, field admin, echo di `theme-vars.blade.php`, dan pemetaan `@theme`.
- [ ] Test di `tests/Feature/Settings/AppearanceSettingsTest.php` lulus.
- [ ] Tidak ada warna hex atau nama font yang ditulis langsung di Blade.

## Dokumen terkait

- [Arsitektur project](arsitektur.md)
- [Panduan menambah section](panduan-section.md)
