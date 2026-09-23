<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Akun super_admin pertama untuk instalasi ini. Kredensial dibaca dari
 * config('seeding.super_admin') — yang bersumber dari `.env`
 * (ADMIN_NAME/ADMIN_EMAIL/ADMIN_PASSWORD), TIDAK ditulis mati di kode,
 * supaya tiap instalasi klien bisa punya kredensial sendiri tanpa
 * mengubah seeder ini (Prinsip I konstitusi). Dibaca lewat config(),
 * bukan env() langsung, supaya tetap benar saat `config:cache` dipakai
 * di production (env() langsung akan mengembalikan null saat di-cache).
 *
 * Idempoten lewat pencarian berdasarkan email — aman dipanggil ulang
 * saat re-seed, tidak membuat duplikat maupun menimpa password akun
 * yang sudah ada.
 *
 * Di lingkungan production, bila ADMIN_PASSWORD tidak diisi di `.env`,
 * seeder ini membangkitkan password acak dan mencetaknya ke konsol —
 * bukan diam-diam memakai password bawaan "password" yang sama persis
 * di setiap instalasi klien (kredensial default yang bisa ditebak adalah
 * celah keamanan nyata untuk starter kit yang di-deploy berulang kali).
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('seeding.super_admin');

        $name = $config['name'];
        $email = $config['email'];
        $configuredPassword = $config['password'];

        $isProduction = app()->environment('production');
        $password = filled($configuredPassword)
            ? $configuredPassword
            : ($isProduction ? Str::password(16) : 'password');

        $admin = User::query()->where('email', $email)->first();

        if ($admin) {
            $this->command?->warn("Akun dengan email {$email} sudah ada — password TIDAK diubah, hanya peran super_admin dipastikan.");
        } else {
            $admin = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);

            $this->command?->info("Akun super_admin dibuat: {$email}");

            if (blank($configuredPassword) && $isProduction) {
                $this->command?->warn("ADMIN_PASSWORD tidak diisi di .env — password acak dibangkitkan: {$password}");
                $this->command?->warn('Catat password ini SEKARANG dan ganti setelah login pertama — tidak akan ditampilkan lagi.');
            }
        }

        $admin->syncRoles(['super_admin']);
    }
}
