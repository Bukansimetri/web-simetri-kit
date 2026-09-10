@props([
    'badge' => 'Solar Panel Terpercaya • Efisiensi Hingga 80%',
    'title' => 'Nyalakan Rumah & Bisnis Anda dengan Energi Matahari',
    'subtitle' => 'Solusi tata surya terdepan untuk efisiensi maksimal dan investasi jangka panjang tanpa mengorbankan estetika hunian Anda.',
    'image' => null,
])

@php
    $heroImage = $image ?: asset('images/mockup/home-1.jpg');
@endphp

<section class="relative pt-32 pb-48 px-6 overflow-hidden bg-on-surface text-white min-h-[640px] flex items-center">
    <div class="absolute inset-0 z-0">
        <img src="{{ $heroImage }}" alt="Instalasi solar panel rooftop modern" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-on-surface/90 via-on-surface/65 to-transparent"></div>
    </div>
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-primary-container/20 rounded-full blur-3xl -z-0 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto w-full relative z-10 py-12">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-xs font-semibold text-white mb-6 backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
                <span>{{ $badge }}</span>
            </div>

            <h1 class="font-headline-xl text-3xl md:text-5xl font-extrabold text-white tracking-tight leading-tight mb-6">
                {{ $title }}
            </h1>

            <p class="text-base md:text-lg text-white/90 font-medium leading-relaxed mb-8 max-w-xl">
                {{ $subtitle }}
            </p>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mb-10">
                <a href="{{ url('/kontak') }}" class="btn-fill bg-primary-container text-white font-bold px-8 py-4 rounded-lg shadow-lg hover:shadow-xl hover:scale-105 transition-all flex items-center justify-center gap-2 group">
                    <span>Konsultasi Gratis</span>
                    <span class="material-symbols-outlined text-lg group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </a>
                <a href="{{ url('/#kalkulator') }}" class="bg-white/10 hover:bg-white/20 border border-white/30 text-white font-semibold px-8 py-4 rounded-lg transition-colors flex items-center justify-center">
                    Pelajari Cara Kerja
                </a>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/15">
                <div class="flex -space-x-2">
                    <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-xs font-bold text-white border-2 border-on-surface">✓</div>
                    <div class="w-8 h-8 rounded-full bg-secondary flex items-center justify-center text-xs font-bold text-white border-2 border-on-surface">★</div>
                    <div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-xs font-bold text-primary border-2 border-on-surface">✦</div>
                </div>
                <div class="text-xs text-white/80">
                    <p class="font-bold text-white">500+ Pelanggan Puas</p>
                    <p class="text-white/60">Terpasang di seluruh wilayah Indonesia</p>
                </div>
            </div>
        </div>
    </div>
</section>
