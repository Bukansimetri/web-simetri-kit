@extends('layouts.public')

@php
    $site = app(\App\Settings\SiteSettings::class);
@endphp

@section('title', \App\Support\Seo\PageTitle::forStatic('home', $site->tagline ?: ''))
@section('meta_description', 'Solusi panel surya untuk rumah, bisnis, dan industri — hitung estimasi hemat listrik Anda.')

@php
    $productFallbacks = [
        asset('images/mockup/home-2.jpg'),
        asset('images/mockup/home-3.jpg'),
        asset('images/mockup/home-4.jpg'),
    ];
@endphp

@php
    // Perlu duplikasi filter berkas hilang milik x-sections.hero-slider di
    // sini (bukan hanya di dalamnya) supaya beranda tahu kapan harus jatuh
    // ke hero bawaan -- lihat contracts/public-render.md §1.
    $liveBanners = $banners->filter(
        fn ($b) => $b->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($b->image_path)
    );
@endphp

@section('content')
    @if ($liveBanners->isNotEmpty())
        <x-sections.hero-slider :banners="$liveBanners" />
    @else
        <x-sections.hero />
    @endif

    <x-sections.calculator />

    {{-- Kalkulator Detail Sistem PLTS -- disembunyikan sementara atas permintaan klien
         (14 Sep 2026). Aktifkan kembali dengan menghapus comment di bawah. --}}
    {{-- <x-sections.calculator-plts /> --}}

    <x-sections.why-choose />

    <x-sections.how-it-works />

    {{-- Produk Kami --}}
    <section class="reveal-element py-32 px-6 mt-12 bg-surface-container-lowest">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="font-headline-xl text-3xl md:text-5xl font-extrabold tracking-tight mb-4 text-primary">Solusi Untuk Setiap Kebutuhan</h2>
                <p class="text-base md:text-lg font-medium max-w-2xl mx-auto leading-relaxed text-secondary">
                    Dirancang spesifik untuk berbagai skala, dari atap hunian minimalis hingga kompleks industri masif.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
                @forelse ($products as $i => $product)
                    @php $featured = $i === 1; $cover = $product->coverImageUrl() ?? ($productFallbacks[$i] ?? $productFallbacks[0]); @endphp
                    <div @class([
                        'bg-white rounded-xl transition-all duration-300 flex flex-col justify-between overflow-hidden group',
                        'border-2 border-primary-container shadow-xl relative -translate-y-2' => $featured,
                        'border border-outline-variant/30 shadow-sm hover:shadow-lg' => ! $featured,
                    ])>
                        @if ($featured)
                            <div class="absolute top-3 right-3 z-10 bg-primary-container text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Terpopuler</div>
                        @endif
                        <div class="{{ $featured ? 'h-56' : 'h-52' }} overflow-hidden">
                            <img src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                        <div class="p-8 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-headline-lg text-2xl text-primary mb-2">{{ $product->name }}</h3>
                                <p class="text-sm text-on-surface-variant leading-relaxed">{{ $product->short_description }}</p>
                            </div>
                            @if ($featured)
                                <a href="{{ url('/produk/'.$product->slug) }}" class="btn-fill text-white text-center py-3 rounded-lg font-semibold text-sm mt-6 bg-primary-container block">Lihat Detail Produk</a>
                            @else
                                <a href="{{ url('/produk/'.$product->slug) }}" class="font-semibold text-sm flex items-center mt-6 text-primary hover:text-primary-container transition-colors group-hover:translate-x-1">
                                    Pelajari lebih lanjut <span class="material-symbols-outlined ml-1 text-sm">arrow_forward</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-on-surface-variant col-span-3 text-center py-12">Produk belum tersedia.</p>
                @endforelse
            </div>

            <div class="text-center mt-12">
                <a href="{{ url('/produk') }}" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-primary-container transition-colors">
                    Lihat semua produk <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Testimoni --}}
    @if ($testimonials->isNotEmpty())
        <section class="reveal-element py-24 px-6 max-w-7xl mx-auto">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="font-headline-xl text-3xl md:text-5xl font-extrabold tracking-tight mb-4 text-primary">Apa Kata Mereka Tentang SUOER?</h2>
                <p class="text-base md:text-lg font-medium leading-relaxed text-secondary">
                    Kisah nyata dari pemilik rumah dan pelaku bisnis yang telah menghemat biaya listrik dan beralih ke energi surya bersama kami.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($testimonials as $i => $testimonial)
                    @php $highlight = $i === 1; @endphp
                    <div @class([
                        'p-8 rounded-xl transition-all duration-300 flex flex-col justify-between hover:-translate-y-2',
                        'bg-surface-container-low border border-primary/30 shadow-md hover:shadow-xl' => $highlight,
                        'bg-white border border-gray-100 shadow-sm hover:shadow-lg' => ! $highlight,
                    ])>
                        <div>
                            <div class="flex items-center gap-1 mb-4 text-primary-container">
                                @for ($s = 1; $s <= 5; $s++)
                                    <span class="material-symbols-outlined text-lg {{ $s <= ($testimonial->rating ?? 5) ? '' : 'text-outline-variant' }}">star</span>
                                @endfor
                            </div>
                            <p class="text-on-surface-variant text-sm leading-relaxed mb-6 {{ $highlight ? 'font-medium' : '' }}">
                                &ldquo;{{ $testimonial->content }}&rdquo;
                            </p>
                        </div>
                        <div class="flex items-center gap-4 pt-4 border-t {{ $highlight ? 'border-outline-variant/30' : 'border-gray-100' }}">
                            @php $hasPhoto = $testimonial->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($testimonial->photo_path); @endphp
                            @if ($hasPhoto)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($testimonial->photo_path) }}" alt="{{ $testimonial->name }}" loading="lazy" decoding="async" class="w-12 h-12 rounded-full object-cover shadow-sm shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-full {{ $i === 2 ? 'bg-secondary' : 'bg-primary-container' }} text-white flex items-center justify-center font-bold text-base shadow-sm shrink-0">
                                    {{ \Illuminate\Support\Str::of($testimonial->name)->explode(' ')->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->take(2)->implode('') }}
                                </div>
                            @endif
                            <div>
                                <h4 class="font-bold text-sm text-primary">{{ $testimonial->name }}</h4>
                                @if ($testimonial->attribution)
                                    <p class="text-xs text-outline font-medium">{{ $testimonial->attribution }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA Penutup --}}
    <section class="reveal-element py-32 px-6 max-w-5xl mx-auto">
        <div class="bg-primary p-12 md:p-16 text-center relative overflow-hidden shadow-md rounded-lg">
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-primary-container rounded-full blur-3xl opacity-40"></div>
            <div class="absolute top-10 right-10 w-32 h-32 bg-surface-container-low rounded-full blur-2xl opacity-20"></div>
            <div class="relative z-10">
                <h2 class="font-headline-xl text-3xl md:text-5xl font-extrabold text-white tracking-tight mb-6">Siap beralih ke<br>energi matahari?</h2>
                <p class="text-white/90 font-medium mb-10 max-w-xl mx-auto text-base md:text-lg leading-relaxed">
                    Mulai perjalanan hijau Anda hari ini. Tim ahli kami siap membantu menganalisa kebutuhan dan memberikan desain sistem gratis.
                </p>
                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                    <a href="{{ $site->whatsappUrl('Halo, saya ingin konsultasi tentang solusi tenaga surya SUOER.') ?: url('/kontak') }}" class="btn-fill w-full sm:w-auto bg-primary-container font-bold px-8 py-4 hover:scale-105 transition-transform shadow-lg inline-flex items-center justify-center gap-2 text-white rounded-lg">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z" /></svg>
                        <span>Chat via WhatsApp</span>
                    </a>
                    <a href="{{ url('/kontak') }}" class="btn-fill w-full sm:w-auto bg-white/10 border border-white/20 text-white font-bold px-8 py-4 hover:bg-white/20 transition-colors rounded-lg text-center">
                        Isi Form Online
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
