@php
    $site = app(\App\Settings\SiteSettings::class);
    $appearance = app(\App\Settings\AppearanceSettings::class);
    $appName = $site->site_name ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ $site->default_language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $appName }} — Sedang Pemeliharaan</title>
    @if ($appearance->favicon_path)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($appearance->favicon_path) }}">
    @endif
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
        p { color: #57534e; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ $appName }} sedang dalam pemeliharaan</h1>
        <p>Kami sedang melakukan pembaruan untuk meningkatkan layanan. Situs akan kembali normal sebentar lagi. Terima kasih atas kesabaran Anda.</p>
    </div>
</body>
</html>
