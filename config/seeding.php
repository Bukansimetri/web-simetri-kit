<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Akun super_admin awal
    |--------------------------------------------------------------------------
    |
    | Dibaca oleh database/seeders/AdminUserSeeder.php. Kredensial di sini
    | HARUS diisi lewat .env per instalasi klien — sengaja tidak ditulis
    | mati di kode (Prinsip I konstitusi). Kosongkan ADMIN_PASSWORD untuk
    | instalasi lokal/dev (jatuh ke "password"); WAJIB diisi eksplisit di
    | .env sebelum seed di production, atau seeder akan membangkitkan
    | password acak dan mencetaknya ke konsol.
    |
    */

    'super_admin' => [
        'name' => env('ADMIN_NAME', 'Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
