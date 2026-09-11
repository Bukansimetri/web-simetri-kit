@php
    $brand = app(\App\Settings\BrandSettings::class);
    $appName = $brand->app_name ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $appName)</title>
    <meta name="description" content="@yield('meta_description', $brand->meta_description ?: 'Solusi panel surya untuk rumah, bisnis, dan industri.')">
    <link rel="canonical" href="{{ url()->current() }}">

    @include('layouts.partials.og-meta')
    @include('layouts.partials.schema-organization')

    {{-- Font heading/body (Manrope, Be Vietnam Pro, dst.) di-bundle saat build lewat
         laravel-vite-plugin/fonts (lihat vite.config.js) dan otomatis di-preload oleh
         @vite. Material Symbols dipakai sebagai icon set, dimuat langsung. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    @if ($brand->favicon_path)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($brand->favicon_path) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-vars')

    @stack('head')
</head>
<body class="font-body-md text-on-surface antialiased" style="background-color: var(--color-background);">
    <x-layout.header />

    <main>
        @yield('content')
    </main>

    <x-layout.footer />

    @stack('scripts')
</body>
</html>
