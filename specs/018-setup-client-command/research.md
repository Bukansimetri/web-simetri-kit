# Research: Setup Client Command

**Feature**: 018-setup-client-command | **Date**: 2026-09-13

## 1. Cara membuat `.env` dari `.env.example`

**Decision**: Command membaca `base_path('.env.example')`, dan jika `base_path('.env')` belum ada, salin isinya (`File::copy` atau `File::put(File::get(...))`) ke `.env`. Jika `.env` sudah ada, langkah ini dilewati dengan pesan info, kecuali flag `--force` diberikan.

**Rationale**: Ini pola standar yang dipakai banyak starter kit Laravel (mis. Laravel Sail/Jetstream installer) — tidak perlu package tambahan, cukup `Illuminate\Filesystem\Filesystem` yang sudah menjadi bagian framework inti.

**Alternatives considered**:
- Memakai command shell `cp .env.example .env` via `Process` — ditolak, tidak portable lintas OS (Windows) dan tanpa manfaat dibanding `File` facade bawaan Laravel.

## 2. Cara mengubah `APP_NAME` di file `.env`

**Decision**: Baca isi `.env` sebagai string, cari baris yang diawali `APP_NAME=` dengan regex, dan ganti nilainya dengan nama yang diberikan (di-quote jika mengandung spasi/karakter khusus). Jika baris `APP_NAME=` tidak ditemukan, tambahkan baris baru.

**Rationale**: Manipulasi berbasis regex/string pada file `.env` adalah pendekatan standar dipakai command Laravel resmi lain (mis. `key:generate` sendiri melakukan hal serupa untuk `APP_KEY`) — tidak butuh parser `.env` pihak ketiga untuk kasus sederhana "ganti satu key". Nilai di-escape dengan membungkus tanda kutip ganda dan meng-escape tanda kutip ganda di dalamnya, konsisten dengan format yang dipahami `vlucas/phpdotenv` (dependency Laravel yang sudah ada).

**Alternatives considered**:
- Package `vlucas/phpdotenv`-based env writer pihak ketiga — ditolak (Prinsip V: hindari dependency baru untuk kebutuhan sesederhana ini; `phpdotenv` sendiri sudah ada sebagai dependency transitif Laravel tapi tidak menyediakan API tulis resmi yang stabil untuk dipakai aplikasi).

## 3. Generate `APP_KEY`

**Decision (direvisi saat implementasi)**: Generate key baru memakai `Illuminate\Encryption\Encrypter::generateKey()` (primitif publik resmi yang juga dipakai command bawaan `key:generate` secara internal), format `'base64:'.base64_encode(...)`, lalu tulis lewat helper penulis `.env` milik command ini sendiri (lihat §2) — BUKAN memanggil `$this->call('key:generate', ...)`. Deteksi "sudah ada key" membaca langsung baris `APP_KEY=` dari isi file `.env` yang sedang diproses.

**Rationale**: Rencana awal (memanggil `key:generate` sebagai sub-command) ternyata bermasalah saat implementasi: `KeyGenerateCommand` bawaan Laravel mendeteksi "key sudah ada" dan membangun pola regex penggantian dari `config('app.key')` — yaitu key yang SUDAH TER-CACHE di memori proses saat ini (dari `.env` asli yang dimuat saat aplikasi boot), bukan dari isi file `.env` yang baru saja ditulis/ditimpa command ini. Ini membuat sub-call ke `key:generate` bisa gagal diam-diam (tidak menemukan pola yang cocok) tepat pada skenario inti fitur ini: instalasi benar-benar baru, di mana `.env` yang baru dibuat punya `APP_KEY` kosong sementara proses PHP yang sedang berjalan mungkin sudah memuat config dari `.env` lain. Membaca & menulis langsung ke file (konsisten dengan cara `APP_NAME` ditangani) menghindari ketergantungan pada state config yang bisa stale, sekaligus membuat command lebih mudah diuji tanpa bergantung pada urutan boot proses PHP tertentu.

**Alternatives considered**:
- Tetap panggil `key:generate` sebagai sub-command (rencana awal) — ditolak setelah ditemukan masalah di atas.
- Generate key manual dengan `random_bytes()` sendiri tanpa memakai `Encrypter::generateKey()` — ditolak, tetap lebih baik reuse primitif resmi Laravel yang sudah menjamin format & panjang key sesuai cipher yang dikonfigurasi (Prinsip V).

## 4. Membersihkan cache (config, route, view, application)

**Decision**: Panggil `config:clear`, `route:clear`, `view:clear`, dan `cache:clear` lewat `$this->call(...)` masing-masing, dijalankan tanpa syarat di setiap eksekusi command (tidak dilewati meski langkah `.env`/key dilewati).

**Rationale**: Ini persis empat command bawaan yang disebutkan literal di deskripsi fitur & konstitusi ("clear cache (config, route, view, application)"). Memanggilnya lewat `$this->call()` (bukan `Artisan::call()` statis) memastikan output tiap command tetap tampil ke konsol, sesuai FR-008 (ringkasan hasil yang jelas).

**Alternatives considered**:
- `php artisan optimize:clear` (satu command yang membungkus semuanya) — dipertimbangkan tapi ditolak karena cakupannya berubah-ubah antar versi Laravel dan bisa mencakup lebih dari 4 jenis cache yang diminta (mis. event cache), sehingga hasilnya kurang eksplisit/dapat diprediksi dibanding memanggil keempat command satu per satu.

## 5. Deteksi & perlindungan penimpaan (`--force`)

**Decision**: Satu flag `--force` pada `app:setup-client` mengontrol baik penimpaan `.env` maupun regenerasi `APP_KEY` (bukan dua flag terpisah). Tanpa `--force`: jika `.env` sudah ada, langkah copy dilewati dengan pesan info; jika `APP_KEY` sudah terisi, langkah generate dilewati dengan pesan info. Dengan `--force`: kedua langkah dijalankan ulang (menimpa `.env` dari template lagi, lalu generate key baru).

**Rationale**: Menyederhanakan antarmuka command (satu flag, bukan dua) sesuai Prinsip V, sambil tetap memenuhi FR-006/FR-007 (tidak menimpa tanpa instruksi eksplisit) dan skenario US3 di spec.md. Ini konsisten dengan pola `--force` yang sudah familiar dari `key:generate` bawaan Laravel sendiri.

**Alternatives considered**:
- Prompt interaktif "apakah Anda yakin?" alih-alih flag — ditolak sebagian: command tetap butuh mode non-interaktif untuk dipakai di script CI/provisioning otomatis (konsisten dengan konvensi `--no-interaction` yang dipakai di seluruh command Artisan proyek ini, lihat CLAUDE.md Boost guidelines). Sebagai gantinya, command TETAP menampilkan prompt konfirmasi standar Laravel ketika dijalankan interaktif tanpa `--force` dan mendeteksi `.env`/key sudah ada, tapi otomatis melewati (bukan gagal) ketika `--no-interaction` diberikan tanpa `--force` — meniru pola `key:generate` bawaan.

## Outstanding NEEDS CLARIFICATION

Tidak ada. Seluruh keputusan teknis memakai command/API bawaan Laravel yang sudah stabil, tanpa dependency baru.
