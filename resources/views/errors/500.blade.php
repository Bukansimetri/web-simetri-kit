@php
    // Halaman ini dirender saat aplikasi sedang bermasalah — TIDAK BOLEH
    // bergantung pada layout publik penuh (header/footer melakukan banyak
    // query: menu, pengaturan, dst). Bila basis data sendiri yang bermasalah,
    // pembacaan pengaturan di bawah ini harus tetap gagal dengan tenang dan
    // jatuh ke pesan bawaan, bukan melempar kesalahan kedua yang menutupi
    // pesan aslinya (FR-017, research.md R6).
    $errorMessage = 'Kami sedang mengalami kendala teknis. Tim kami sudah diberi tahu dan sedang menanganinya.';
    $appName = config('app.name');

    try {
        $site500 = app(\App\Settings\SiteSettings::class);
        $errorMessage = $site500->error_500_message ?: $errorMessage;
        $appName = $site500->site_name ?: $appName;
    } catch (\Throwable $e) {
        // Pengaturan tidak terbaca (mis. basis data down) — pakai fallback
        // di atas, jangan biarkan exception ini menggantikan halaman 500.
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }} — Gangguan Sistem</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f4;
            color: #1c1917;
            text-align: center;
            padding: 24px;
        }
        .card { max-width: 28rem; }
        h1 { font-size: 1.5rem; margin-bottom: 0.75rem; }
        p { color: #57534e; line-height: 1.6; margin-bottom: 1.5rem; }
        a { display: inline-block; padding: 0.75rem 1.5rem; background: #1c1917; color: #fff; border-radius: 0.5rem; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ $appName }}</h1>
        <p>{{ $errorMessage }}</p>
        <a href="{{ url('/') }}">Kembali ke Beranda</a>
    </div>
</body>
</html>
