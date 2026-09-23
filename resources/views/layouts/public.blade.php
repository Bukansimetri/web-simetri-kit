@php
    $site = app(\App\Settings\SiteSettings::class);
    $appearance = app(\App\Settings\AppearanceSettings::class);
    $seo = app(\App\Settings\SeoSettings::class);
    $social = app(\App\Settings\SocialSettings::class);
    $script = app(\App\Settings\ScriptSettings::class);
    $appName = $site->site_name ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ $site->default_language }}" class="scroll-smooth">
<head>
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Slot skrip kepala dokumen (FR-041) — dimuat apa adanya sebagai kode
         mentah, TIDAK di-escape dengan sengaja. Pengaman fitur ini adalah
         pembatasan peran super_admin pada ScriptSettingsPage (FR-045), bukan
         penyaringan isi di sini — jangan ubah {!! !!} ini menjadi {{ }},
         itu akan mematikan seluruh fitur pemasangan skrip pihak ketiga.
         Kategori selain "none" dibungkus <x-layout.gated-script> agar tidak
         dieksekusi sebelum pengunjung menyetujui (FR-054). --}}
    @if ($script->head_scripts)
        <x-layout.gated-script :category="$script->head_scripts_consent">{!! $script->head_scripts !!}</x-layout.gated-script>
    @endif
    @if ($script->custom_css)
        <x-layout.gated-script :category="$script->custom_css_consent"><style>{!! $script->custom_css !!}</style></x-layout.gated-script>
    @endif

    <title>@yield('title', \App\Support\Seo\PageTitle::forStatic('home', $site->tagline ?: ''))</title>
    <meta name="description" content="@yield('meta_description', $seo->default_meta_description ?: 'Solusi panel surya untuk rumah, bisnis, dan industri.')">
    @if ($seo->meta_keywords)
        <meta name="keywords" content="{{ implode(', ', $seo->meta_keywords) }}">
    @endif
    <link rel="canonical" href="{{ $seo->default_canonical_url ?: url()->current() }}">

    @include('layouts.partials.og-meta')
    @include('layouts.partials.schema-organization')
    @include('layouts.partials.head-extra')

    {{-- Font heading/body (Manrope, Be Vietnam Pro, dst.) di-bundle saat build lewat
         laravel-vite-plugin/fonts (lihat vite.config.js) dan otomatis di-preload oleh
         @vite. Material Symbols (icon set) dimuat non-blocking (AMC-225 FR-009) —
         media="print" + onload swap supaya tidak menghalangi render konten utama;
         <noscript> fallback untuk pengunjung tanpa JavaScript. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"></noscript>

    @if ($appearance->favicon_path)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($appearance->favicon_path) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-vars')

    @stack('head')
</head>
<body class="font-body-md text-on-surface antialiased" style="background-color: var(--color-background);">
    {{-- Slot skrip awal body (FR-041) — sama seperti slot head di atas,
         sengaja tidak di-escape; lihat catatan FR-044/FR-045 di <head>. --}}
    @if ($script->body_start_scripts)
        <x-layout.gated-script :category="$script->body_start_scripts_consent">{!! $script->body_start_scripts !!}</x-layout.gated-script>
    @endif

    <x-layout.header />

    <main>
        @yield('content')
    </main>

    <x-layout.footer />

    @if ($script->footer_scripts)
        <x-layout.gated-script :category="$script->footer_scripts_consent">{!! $script->footer_scripts !!}</x-layout.gated-script>
    @endif

    @if ($script->cookie_consent_enabled)
        <x-layout.cookie-consent :message="$script->cookie_banner_message" />
    @endif

    @stack('scripts')

    {{-- Slot skrip akhir body dan JS khusus (FR-041, FR-043) — sengaja tidak
         di-escape; lihat catatan FR-044/FR-045 di <head>. --}}
    @if ($script->body_end_scripts)
        <x-layout.gated-script :category="$script->body_end_scripts_consent">{!! $script->body_end_scripts !!}</x-layout.gated-script>
    @endif
    @if ($script->custom_js)
        <x-layout.gated-script :category="$script->custom_js_consent"><script>{!! $script->custom_js !!}</script></x-layout.gated-script>
    @endif
</body>
</html>
