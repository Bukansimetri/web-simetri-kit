@extends('layouts.public')

@section('title', ($brand = app(\App\Settings\BrandSettings::class))->app_name ?: config('app.name'))
@section('meta_description', 'Solusi panel surya untuk rumah, bisnis, dan industri — hitung estimasi hemat listrik Anda.')

@section('content')
    <x-sections.hero />

    <x-sections.calculator />

    <x-sections.why-choose />

    <x-sections.how-it-works />

    {{-- Produk Kami — data dari Product seed (FR-008), bukan hardcode --}}
    <section class="bg-primary py-32 px-6 mt-12 relative overflow-hidden">
        <div class="max-w-7xl mx-auto relative z-10">
            <div class="text-center mb-16">
                <h2 class="font-headline-lg text-headline-lg text-white mb-4">Solusi Untuk Setiap Kebutuhan</h2>
                <p class="text-lg font-body-md text-body-md text-white/70 max-w-2xl mx-auto">
                    Dirancang spesifik untuk berbagai skala, dari atap hunian minimalis hingga kompleks industri masif.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @forelse ($products as $product)
                    <div class="bg-white/5 overflow-hidden border border-white/10 hover:bg-white/10 hover:-translate-y-2 transition-all duration-300 flex flex-col rounded-lg">
                        <div class="h-48 overflow-hidden bg-white/10"></div>
                        <div class="p-8 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-headline-lg text-headline-lg text-2xl text-white mb-2">{{ $product->name }}</h3>
                                <p class="text-white/70 text-sm">{{ $product->short_description }}</p>
                            </div>
                            <a href="{{ url('/produk/'.$product->slug) }}" class="font-semibold text-sm flex items-center mt-6 hover:translate-x-2 transition-transform text-white">
                                Pelajari lebih lanjut <span class="material-symbols-outlined ml-1 text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-white/70 col-span-3 text-center">Produk belum tersedia.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- CTA Penutup --}}
    <section class="py-32 px-6 max-w-5xl mx-auto">
        <div class="bg-primary p-12 md:p-16 text-center relative overflow-hidden shadow-md rounded-lg">
            <div class="relative z-10">
                <h2 class="font-headline-xl text-headline-xl md:text-5xl text-white mb-6">Siap beralih ke<br>energi matahari?</h2>
                <p class="text-white/80 mb-10 max-w-xl mx-auto text-lg">
                    Mulai perjalanan hijau Anda hari ini. Tim ahli kami siap membantu menganalisa kebutuhan dan memberikan desain sistem gratis.
                </p>
                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                    <a href="{{ url('/kontak') }}" class="btn-fill w-full sm:w-auto bg-primary-container font-bold px-8 py-4 hover:scale-105 transition-transform shadow-lg inline-flex items-center justify-center gap-2 text-white rounded-lg">
                        Chat via WhatsApp
                    </a>
                    <a href="{{ url('/kontak') }}" class="btn-fill w-full sm:w-auto bg-white/10 border border-white/20 text-white font-bold px-8 py-4 hover:bg-white/20 transition-colors rounded-lg">
                        Isi Form Online
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
