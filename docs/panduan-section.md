# Panduan Menambah Section

**Terakhir diperbarui**: 2026-09-26

Panduan ini untuk developer yang perlu menambah **section baru** di halaman publik (misal section "Sertifikasi" di Home) atau membuat varian dari section yang sudah ada. Baca [arsitektur project](arsitektur.md) lebih dulu supaya peta folder dan alur cache sudah dikenal. Untuk mengubah warna, font, atau token tema, pakai [panduan tema](panduan-tema.md).

## Batasan saat ini

- **Belum ada pemilih varian section di panel admin** (AMC-221, ditunda). Varian saat ini dibuat sebagai komponen bernama terpisah dan dipilih di kode halaman. Contoh nyata: Home memilih antara `resources/views/components/sections/hero.blade.php` dan `resources/views/components/sections/hero-slider.blade.php` di `resources/views/pages/home.blade.php`. Jangan membangun pemilih varian sendiri tanpa spec; kerjakan lewat AMC-221.
- **Page builder dilarang** (Principle III di [constitution](../.specify/memory/constitution.md)). Fleksibilitas visual baru harus datang sebagai section bernama, bukan editor tata letak bebas.

## Pilih sumber data

Putuskan dulu dari mana isi section datang. Ini menentukan langkah 3 dan 6 di bawah.

| Kondisi | Sumber data | Contoh nyata |
|---|---|---|
| Satu blok konten tetap, jumlah field kecil, jarang berubah (judul, teks, beberapa poin) | **Pengaturan situs** (kelas di `app/Settings/`) | `app/Settings/AboutPageSettings.php` dipakai `resources/views/pages/tentang-kami.blade.php` |
| Daftar item yang dikelola admin: tambah, hapus, urutkan, aktif/nonaktif, punya gambar | **Modul konten** (model + resource admin) | Testimonials, lihat [contoh modul nyata](#contoh-modul-nyata-testimonials) |
| Teks desain yang sama untuk semua klien dan tidak berisi data klien | Langsung di Blade | `resources/views/components/sections/how-it-works.blade.php` |

Data klien (nama, alamat, angka, foto) tidak boleh ditulis langsung di Blade. `tests/Feature/Public/NoHardcodedClientDataTest.php` menjaga hal ini.

## Langkah-langkah

1. **Buat komponen** di `resources/views/components/sections/` (nama file huruf kecil dengan tanda hubung). Deklarasikan data yang dibutuhkan dengan `@props([...])` di baris pertama, seperti `resources/views/components/sections/testimonials.blade.php`.
2. **Pakai token tema**, bukan nilai tetap. Untuk warna gunakan class seperti `text-primary`, `bg-secondary`, `bg-surface-container-low`; untuk font gunakan `font-headline-lg`, `font-headline-xl`, `font-body-md`. Semua token didefinisikan di `resources/css/app.css`. Jangan menulis kode warna hex atau nama font langsung di Blade, karena section tidak akan mengikuti pengaturan **Tampilan**.
3. **Hubungkan data.**
   - Pengaturan situs: tambah properti di kelas Settings, buat migrasi nilai default dengan `php artisan make:settings-migration`, tambahkan field di halaman admin terkait di `app/Filament/Pages/`, lalu baca nilainya di view halaman.
   - Modul konten: buat model, migrasi, factory, dan resource admin (`php artisan make:model`, `php artisan make:filament-resource`), tempatkan resource di grup navigasi yang sesuai, lalu ambil datanya di controller di `app/Http/Controllers/Public/`.
4. **Pasang di halaman** dengan `<x-sections.nama-file :prop="$data" />` di view di `resources/views/pages/`.
5. **Tangani empty state.** Bungkus isi section dengan `@if ($items->isNotEmpty()) ... @endif` (atau sejenisnya) supaya section hilang, bukan error atau kosong menggantung, saat data belum diisi. Untuk item bergambar, cek juga file gambar benar-benar ada di disk `public`, seperti `resources/views/components/sections/client-logos.blade.php`.
6. **Cache.** Hanya data yang dikembalikan closure `rememberPublicPage` di controller yang di-cache 5 menit. Aturannya:
   - Data dari **Settings yang dibaca langsung di Blade** tidak ikut di-cache, jadi tidak perlu invalidasi.
   - Data dari **query di controller** yang di-cache perlu dihapus saat berubah: tambahkan hook `saved`/`deleted` di model yang menghapus key halaman (contoh `app/Models/Banner.php`), atau `Cache::forget(...)` di dalam `save()` halaman pengaturan (contoh `app/Filament/Pages/AboutPageSettingsPage.php`). Jika key halaman dipakai bersama (misal Home dan Tentang Kami menampilkan data yang sama), hapus semua key yang terpengaruh.
7. **Tulis test** (wajib untuk modul, Principle IV): satu test render publik (section tampil saat data ada, tidak tampil saat kosong) dan, untuk modul konten atau field pengaturan baru, satu test admin. Tempatkan di `tests/Feature/Pages/` (render halaman) dan `tests/Feature/Admin/` atau `tests/Feature/Settings/` (admin).
8. **Rapikan dan build.** Jalankan `vendor/bin/pint --dirty --format agent`, jalankan test yang terkait dengan `php artisan test --compact --filter=NamaTest`, lalu `npm run build` bila ada perubahan class Tailwind atau CSS.

## Contoh lengkap: section "Sertifikasi" di Home

Skenario: klien ingin blok "Sertifikasi" di Home berisi judul dan beberapa poin (ikon, nama, keterangan). Isinya sedikit dan jarang berubah, jadi sumber datanya **pengaturan situs**. Semua kode di bawah adalah contoh; file-nya belum ada di repo.

**1. Migrasi nilai default** (`php artisan make:settings-migration AddCertificationsToSiteSettings`, isi `up()`):

```php
$this->migrator->add('site.sertifikasi_heading', null);
$this->migrator->add('site.sertifikasi_items', json_encode([]));
```

**2. Properti di `app/Settings/SiteSettings.php`** (item disimpan sebagai JSON string, mengikuti pola `misi_items` di `app/Settings/AboutPageSettings.php`):

```php
public ?string $sertifikasi_heading;

public ?string $sertifikasi_items;

/**
 * @return array<int, array{icon: string, title: string, description: string}>
 */
public function sertifikasiItems(): array
{
    return json_decode($this->sertifikasi_items ?? '[]', true) ?: [];
}
```

**3. Field admin** di `app/Filament/Pages/SiteSettingsPage.php`: tambah `TextInput::make('sertifikasi_heading')` dan `Repeater::make('sertifikasi_items')` (field `icon`, `title`, `description`) di `form()`, isi nilainya di `mount()`, dan simpan di `save()`:

```php
$settings->sertifikasi_heading = $data['sertifikasi_heading'] ?? null;
$settings->sertifikasi_items = json_encode($data['sertifikasi_items'] ?? []);
```

**4. Komponen**: file baru di folder `resources/views/components/sections/`, bernama `certifications.blade.php` (perhatikan token tema dan empty state):

```blade
@props(['heading' => null, 'items' => []])

@if (filled($heading) && count($items) > 0)
    <section class="py-24 px-6 bg-surface-container-low">
        <div class="max-w-7xl mx-auto">
            <h2 class="font-headline-lg text-headline-lg md:text-5xl text-primary text-center mb-16">{{ $heading }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($items as $item)
                    <div class="bg-white p-8 rounded-lg shadow-sm">
                        <span class="material-symbols-outlined text-3xl text-secondary">{{ $item['icon'] }}</span>
                        <h3 class="font-headline-lg text-xl text-primary mt-4">{{ $item['title'] }}</h3>
                        <p class="font-body-md text-on-surface-variant mt-2">{{ $item['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
```

**5. Pasang di Home** (`resources/views/pages/home.blade.php`), setelah section yang diinginkan. `$site` sudah tersedia di file ini:

```blade
<x-sections.certifications :heading="$site->sertifikasi_heading" :items="$site->sertifikasiItems()" />
```

**6. Cache**: tidak ada yang perlu dilakukan. `$site` dibaca langsung di Blade, bukan dari hasil `rememberPublicPage`, jadi perubahan dari admin langsung tampil.

**7. Test** (`php artisan make:test --phpunit Pages/HomeCertificationsTest`):

```php
public function test_certifications_section_is_shown_when_configured(): void
{
    $settings = app(SiteSettings::class);
    $settings->sertifikasi_heading = 'Sertifikasi Kami';
    $settings->sertifikasi_items = json_encode([
        ['icon' => 'verified', 'title' => 'ISO 9001', 'description' => 'Manajemen mutu.'],
    ]);
    $settings->save();

    $this->get('/')->assertOk()->assertSee('Sertifikasi Kami')->assertSee('ISO 9001');
}

public function test_certifications_section_is_hidden_when_empty(): void
{
    $this->get('/')->assertOk()->assertDontSee('Sertifikasi Kami');
}
```

Tambahkan juga satu test admin yang menyimpan field baru lewat halaman pengaturan, meniru `tests/Feature/Settings/AboutPageSettingsTest.php`.

**8. Cek akhir**: ubah warna primer di admin (**Pengaturan Situs → Tampilan**) lalu buka Home. Judul dan ikon section harus ikut berubah warna. Jika tidak, ada warna atau font yang tertulis langsung di Blade.

## Contoh modul nyata: Testimonials

Untuk jalur modul konten, jangan membuat pola baru; tiru Testimonials, modul lengkap terkecil yang memakai semua pola di atas.

| Peran | File |
|---|---|
| Migrasi | `database/migrations/2026_09_06_222938_create_testimonials_table.php` |
| Model + hook cache | `app/Models/Testimonial.php` |
| Resource admin | `app/Filament/Resources/TestimonialResource.php` |
| Query di controller (Home) | `app/Http/Controllers/Public/HomeController.php` |
| Query di controller (Tentang Kami) | `app/Http/Controllers/Public/AboutController.php` |
| Section + empty state | `resources/views/components/sections/testimonials.blade.php` |
| Seeder demo | `database/seeders/TestimonialSeeder.php` |
| Test admin | `tests/Feature/Admin/TestimonialResourceTest.php` |
| Test halaman publik | `tests/Feature/Pages/AboutPageTestimonialsTest.php` |

Catatan: modul ini tampil di dua halaman, tetapi model hanya menghapus cache Tentang Kami; lihat [hal yang perlu diperhatikan](arsitektur.md#hal-yang-perlu-diperhatikan). Saat modul baru Anda tampil di lebih dari satu halaman, hapus key semua halaman itu.

## Checklist selesai

- [ ] Warna dan font memakai token tema, tidak ada hex atau nama font di Blade.
- [ ] Data klien tidak ditulis di Blade; `NoHardcodedClientDataTest` lulus.
- [ ] Empty state aman (section tidak tampil atau tampil wajar, tanpa error).
- [ ] Cache diinvalidasi untuk semua halaman yang menampilkan datanya (atau data dibaca langsung dari Settings).
- [ ] Test render publik dan test admin ada dan lulus.
- [ ] `vendor/bin/pint --dirty --format agent` sudah dijalankan, dan `npm run build` bila ada perubahan CSS.
- [ ] Bila menambah modul baru, tabel modul di [arsitektur project](arsitektur.md) diperbarui.

## Dokumen terkait

- [Arsitektur project](arsitektur.md)
- [Panduan tema](panduan-tema.md)
