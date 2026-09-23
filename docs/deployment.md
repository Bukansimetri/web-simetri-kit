# Panduan Deployment

Dokumen ini adalah rujukan untuk men-deploy instalasi klien Web Solarpanel Kit (Simetri) ke server produksi. Ada dua jalur berbeda tergantung jenis hosting klien:

- **VPS** (akses root penuh) — lihat [Deploy ke VPS](#deploy-ke-vps)
- **Shared hosting berbasis cPanel** (akses terbatas, umum dipakai penyedia hosting Indonesia untuk paket ekonomis) — lihat [Deploy ke Shared Hosting cPanel](#deploy-ke-shared-hosting-cpanel)

Pilih jalur yang sesuai paket hosting klien. Anda tidak perlu membaca jalur yang lain untuk menyelesaikan deploy Anda.

Sebelum memulai, pastikan repositori klien sudah disiapkan mengikuti [`docs/versioning-strategi-klien.md`](versioning-strategi-klien.md).

## Requirement Server

Berlaku untuk kedua jenis hosting:

| Komponen | Kebutuhan |
|---|---|
| PHP | **8.3 atau lebih baru** (`composer.json`: `"php": "^8.3"`) |
| Ekstensi PHP | `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, **`gd`** |
| Database | MySQL 8+ atau MariaDB 10.3+ |
| Web server | Apache (dengan `mod_rewrite` aktif) atau Nginx |
| Composer | Versi terbaru (untuk instalasi dependency PHP) |
| Node.js + npm | Untuk build asset frontend (`npm run build`) |
| Batas upload PHP | `upload_max_filesize` ≥ `10M`, `post_max_size` ≥ `12M`, `memory_limit` ≥ `256M` (lihat `php.ini`) |

> **Penting soal `gd`**: ekstensi ini sering terlewat di panduan Laravel generik, padahal **wajib** untuk kit ini — dipakai `App\Support\ImageUploads` untuk mengonversi setiap gambar yang diupload (produk, portfolio, banner, tim, dll.) ke format WebP. ❌ **Jangan lanjutkan deploy** bila `gd` tidak aktif — upload gambar akan gagal di seluruh modul konten.

> **Penting soal batas upload**: beberapa form admin (mis. Banner) mengizinkan upload gambar hingga 10MB di level aplikasi (Filament), tapi PHP sendiri menolak lebih dulu bila `upload_max_filesize`/`post_max_size` di `php.ini` lebih kecil dari itu — defaultnya sering `2M`/`8M`. `memory_limit` juga perlu dinaikkan karena `App\Support\ImageUploads` mendekode gambar ke bitmap mentah di memori sebelum dikonversi ke WebP, yang bisa jauh lebih besar dari ukuran file aslinya untuk foto beresolusi tinggi. Di shared hosting cPanel, ini biasanya diatur lewat menu **MultiPHP INI Editor**, bukan file `php.ini` langsung.

## Deploy ke VPS

Asumsi: VPS baru, Ubuntu LTS (atau distribusi Linux sejenis), belum ada stack apa pun terpasang, Anda punya akses root/sudo.

### 1. Instalasi stack

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server php8.3-fpm php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-bcmath php8.3-gd php8.3-cli php8.3-curl unzip git
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
```

Sesuaikan versi PHP di atas (`php8.3-*`) dengan versi 8.3+ yang tersedia di repository distribusi Anda.

### 2. Ambil kode & install dependency

```bash
git clone https://github.com/Bukansimetri/client-acme.git /var/www/client-acme
cd /var/www/client-acme
composer install --no-dev --optimize-autoloader
npm install --ignore-scripts
npm run build
```

(Lihat [`docs/versioning-strategi-klien.md`](versioning-strategi-klien.md) untuk cara clone yang benar agar histori `upstream` tersambung.)

### 3. Konfigurasi environment & aplikasi

```bash
php artisan app:setup-client "Nama Klien"
```

(Lihat detail command ini di `specs/018-setup-client-command/quickstart.md`.) Setelah itu, edit `.env` untuk mengisi kredensial database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), `APP_URL` (domain final klien), `APP_ENV=production`, dan `APP_DEBUG=false`.

```bash
php artisan storage:link
php artisan migrate --force
sudo chown -R www-data:www-data /var/www/client-acme/storage /var/www/client-acme/bootstrap/cache
```

> **Banner Hero Slider**: `php artisan migrate --force` di atas juga
> menjalankan migration `add_hero_fields_to_banners_table`, yang otomatis
> mengisi banner ber-urutan terkecil dengan konten hero contoh **hanya
> bila** kolom judulnya masih kosong — aman dijalankan di basis data klien
> yang sudah berisi banner lama. Tidak ada langkah manual tambahan.

### 4. Konfigurasi web server

Arahkan document root **Nginx/Apache ke folder `public/`** proyek (BUKAN ke root proyek). Contoh virtual host Nginx minimal:

```nginx
server {
    listen 80;
    server_name domainklien.com;
    root /var/www/client-acme/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5. Proses latar belakang: queue worker & scheduler

VPS punya akses root sehingga proses latar belakang bisa dijalankan **persisten** (auto-restart bila crash) lewat Supervisor:

```bash
sudo apt install -y supervisor
```

Buat `/etc/supervisor/conf.d/client-acme-worker.conf`:

```ini
[program:client-acme-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/client-acme/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/client-acme/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start client-acme-worker:*
```

Tambahkan scheduler ke crontab (`crontab -e -u www-data`):

```
* * * * * cd /var/www/client-acme && php artisan schedule:run >> /dev/null 2>&1
```

### 6. HTTPS

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domainklien.com
```

Certbot mendaftarkan auto-renewal otomatis (via systemd timer/cron bawaan) — verifikasi dengan `sudo certbot renew --dry-run`. **Sertifikat Let's Encrypt berlaku 90 hari** dan wajib diperbarui otomatis; jangan andalkan pemasangan manual sekali saja.

### Selesai — lanjutkan ke:
- [`docs/checklist-ga4-setup.md`](checklist-ga4-setup.md)
- [`docs/checklist-go-live.md`](checklist-go-live.md)

---

## Deploy ke Shared Hosting cPanel

Asumsi: akun shared hosting cPanel baru. **Jangan asumsikan akses SSH/terminal tersedia** — beberapa paket entry-level Indonesia tidak menyediakannya.

### 1. Cek & pilih versi PHP + ekstensi

Buka cPanel → **"Select PHP Version"** atau **"MultiPHP Manager"** untuk domain klien:
1. Pilih PHP **8.3** atau lebih baru.
2. Aktifkan ekstensi: `mbstring`, `pdo_mysql`, `xml`, `bcmath`, `fileinfo`, **`gd`**, `curl`.

❌ **JANGAN lanjutkan** ke langkah berikutnya bila hosting tsb tidak menyediakan PHP 8.3+ atau tidak bisa mengaktifkan `gd` — upgrade paket hosting atau pindah provider terlebih dahulu.

### 2. Upload kode aplikasi

**Jalur A — bila SSH/Terminal tersedia** (banyak paket cPanel menengah-atas menyediakannya, cek menu "Terminal" di cPanel):

```bash
git clone https://github.com/Bukansimetri/client-acme.git
cd client-acme
composer install --no-dev --optimize-autoloader
npm install --ignore-scripts && npm run build
```

**Jalur B — bila SSH tidak tersedia**: jalankan di komputer lokal Anda, lalu upload hasilnya:

```bash
git clone https://github.com/Bukansimetri/client-acme.git
cd client-acme
composer install --no-dev --optimize-autoloader
npm install --ignore-scripts && npm run build
```

Upload SELURUH folder proyek (termasuk `vendor/` dan `public/build/` hasil build) via **File Manager cPanel** atau FTP/SFTP ke server.

### 3. Document root & struktur folder publik

Laravel butuh folder publik yang diakses browser HANYA berisi `public/` proyek — bukan seluruh kode aplikasi (config, `.env`, model, dll. tidak boleh bisa diakses langsung lewat URL).

**Opsi A — cPanel mengizinkan Document Root kustom** (cek menu "Domains" → edit domain → field "Document Root"): upload seluruh proyek ke luar `public_html/` (mis. `~/client-acme/`), lalu arahkan Document Root domain ke `~/client-acme/public`.

**Opsi B — cPanel TIDAK mengizinkan Document Root kustom** (umum di paket termurah):
1. Upload seluruh isi proyek (kecuali folder `public/`) ke satu folder DI LUAR `public_html/`, mis. `~/client-acme-app/`.
2. Upload ISI folder `public/` (bukan foldernya, isinya) langsung ke `public_html/`.
3. Edit `public_html/index.php`, ubah dua baris `require`:
   ```php
   require __DIR__.'/../client-acme-app/vendor/autoload.php';
   $app = require_once __DIR__.'/../client-acme-app/bootstrap/app.php';
   ```

⚠️ Verifikasi setelah deploy: coba akses `https://domainklien.com/.env` dan `https://domainklien.com/../client-acme-app/.env` dari browser — keduanya **harus** menghasilkan 403/404, bukan menampilkan isi file.

### 4. Konfigurasi environment & aplikasi

```bash
php artisan app:setup-client "Nama Klien"
```

Isi `.env`: kredensial database (buat database + user MySQL lewat cPanel "MySQL Databases"), `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`.

```bash
php artisan storage:link
php artisan migrate --force
```

Bila `storage:link` gagal karena tidak ada akses SSH untuk menjalankannya: cek apakah cPanel menyediakan Terminal berbasis web (banyak yang menyediakan). Bila benar-benar tidak ada cara membuat symlink, sebagai jalan terakhir (bukan solusi ideal) salin isi `storage/app/public/` ke `public_html/storage/` secara manual setiap kali ada upload baru.

### 5. Proses latar belakang: queue & scheduler via Cron Jobs

Shared hosting **tidak mendukung proses persisten** (tidak ada Supervisor/systemd untuk aplikasi Anda) — gunakan **Cron Jobs** cPanel sebagai gantinya. Buka cPanel → **"Cron Jobs"**, tambahkan dua entri terjadwal tiap menit:

```
* * * * * cd /home/namauser/client-acme-app && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
* * * * * cd /home/namauser/client-acme-app && php artisan schedule:run >> /dev/null 2>&1
```

`--stop-when-empty` membuat worker berhenti sendiri setelah antrean kosong — aman dipanggil ulang setiap menit tanpa menumpuk proses.

**Alternatif bila Cron Jobs sama sekali tidak tersedia** (sangat jarang, tapi mungkin di paket super-terbatas): set `QUEUE_CONNECTION=sync` di `.env`. Ini membuat job (mis. kirim notifikasi form kontak) diproses langsung saat request, tanpa antrean — memperlambat response time sedikit, tapi tetap berfungsi tanpa cron.

### 6. HTTPS

cPanel modern umumnya menyediakan **AutoSSL** (Let's Encrypt) otomatis. Buka cPanel → **"SSL/TLS Status"**, pastikan AutoSSL aktif untuk domain klien — sertifikat akan terbit dan diperbarui otomatis tanpa konfigurasi tambahan. Bila AutoSSL tidak tersedia, hubungi provider hosting untuk mengaktifkannya atau pasang manual lewat menu "SSL/TLS".

### Selesai — lanjutkan ke:
- [`docs/checklist-ga4-setup.md`](checklist-ga4-setup.md)
- [`docs/checklist-go-live.md`](checklist-go-live.md)
