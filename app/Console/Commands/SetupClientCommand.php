<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\File;

#[Signature('app:setup-client {name : Nama aplikasi untuk klien ini} {--force : Timpa .env dan APP_KEY yang sudah ada}')]
#[Description('Menyiapkan instalasi baru untuk klien: generate .env, set nama aplikasi, generate APP_KEY, dan bersihkan cache')]
class SetupClientCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = trim((string) $this->argument('name'));

        if ($name === '') {
            $this->components->error('Nama aplikasi tidak boleh kosong.');

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');

        if (! $this->setupEnvironmentFile($name, $force)) {
            return self::FAILURE;
        }

        $this->setupApplicationKey($force);
        $this->clearCaches();

        $this->components->info('Setup klien selesai.');

        return self::SUCCESS;
    }

    private function envPath(): string
    {
        return $this->laravel->environmentFilePath();
    }

    private function envExamplePath(): string
    {
        return $this->laravel->environmentPath().DIRECTORY_SEPARATOR.'.env.example';
    }

    /**
     * Salin .env dari .env.example (bila belum ada) dan set APP_NAME.
     * Dilewati sepenuhnya bila .env sudah ada dan --force tidak diberikan
     * (FR-006, US3) — mengembalikan false hanya bila terjadi kegagalan fatal.
     */
    private function setupEnvironmentFile(string $name, bool $force): bool
    {
        $envPath = $this->envPath();
        $envExists = File::exists($envPath);

        if ($envExists && ! $force) {
            $this->components->warn('.env sudah ada — dilewati (gunakan --force untuk menimpa).');

            return true;
        }

        if (! File::exists($this->envExamplePath())) {
            $this->components->error('.env.example tidak ditemukan — tidak dapat membuat .env.');

            return false;
        }

        File::copy($this->envExamplePath(), $envPath);
        $this->setEnvValue($envPath, 'APP_NAME', $name);

        $this->components->task('Membuat .env dari .env.example', fn () => true);

        return true;
    }

    /**
     * Generate APP_KEY baru memakai generator resmi Laravel (Encrypter::
     * generateKey — primitif sama yang dipakai command `key:generate`
     * bawaan), lalu tulis lewat helper .env kita sendiri. Tidak memanggil
     * `key:generate` sebagai sub-command karena command tsb mendeteksi
     * "key sudah ada" dari config ter-cache proses saat ini (bisa berbeda
     * dari isi file .env yang baru saja kita tulis/timpa) — deteksi di sini
     * SENGAJA membaca langsung dari isi file .env (research.md #3, revisi
     * saat implementasi).
     */
    private function setupApplicationKey(bool $force): void
    {
        $envPath = $this->envPath();
        $currentKey = File::exists($envPath) ? $this->currentEnvValue($envPath, 'APP_KEY') : null;

        if (filled($currentKey) && ! $force) {
            $this->components->warn('APP_KEY sudah ada — dilewati (gunakan --force untuk menimpa).');

            return;
        }

        $key = 'base64:'.base64_encode(Encrypter::generateKey(config('app.cipher')));
        $this->setEnvValue($envPath, 'APP_KEY', $key);
        config(['app.key' => $key]);

        $this->components->task('Generate APP_KEY', fn () => true);
    }

    /**
     * Bersihkan cache config/route/view/application tanpa syarat pada
     * setiap eksekusi (FR-005, US2) — tidak bergantung pada apakah langkah
     * .env/APP_KEY di atas dijalankan atau dilewati.
     */
    private function clearCaches(): void
    {
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('cache:clear');
    }

    /**
     * Baca nilai KEY= saat ini dari file .env, atau null bila baris
     * tersebut tidak ada/kosong.
     */
    private function currentEnvValue(string $envPath, string $key): ?string
    {
        $contents = File::get($envPath);

        if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $contents, $matches) !== 1) {
            return null;
        }

        $value = trim($matches[1]);

        return $value === '' ? null : trim($value, '"');
    }

    /**
     * Set/ganti satu baris KEY= pada file .env, membungkus nilai dengan
     * tanda kutip ganda bila mengandung spasi/karakter khusus, dan
     * meng-escape tanda kutip ganda di dalamnya (research.md #2).
     */
    private function setEnvValue(string $envPath, string $key, string $value): void
    {
        $formattedValue = preg_match('/\s|["\'#]/', $value) === 1
            ? '"'.str_replace('"', '\"', $value).'"'
            : $value;

        $contents = File::get($envPath);
        $pattern = '/^'.preg_quote($key, '/').'=.*/m';

        $contents = preg_match($pattern, $contents) === 1
            ? preg_replace($pattern, "{$key}={$formattedValue}", $contents)
            : rtrim($contents)."\n{$key}={$formattedValue}\n";

        File::put($envPath, $contents);
    }
}
