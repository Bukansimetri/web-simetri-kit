@php
    $brand = app(\App\Settings\BrandSettings::class);
    $appName = $brand->app_name ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', $appName)</title>
    <meta name="description" content="@yield('meta_description', 'Solusi panel surya untuk rumah, bisnis, dan industri.')">

    @include('layouts.partials.og-meta')

    {{-- Material Symbols dipakai sebagai icon set di seluruh mockup — bukan bagian dari
         sistem font kurasi (FR-004), jadi tetap dimuat lewat Google Fonts langsung. --}}
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
