@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', 'Tentang Kami — '.$appName)
@section('meta_description', 'Mengenal '.$appName.' lebih dekat — visi, misi, dan nilai-nilai kami dalam menghadirkan solusi energi surya.')

@section('content')
    <section class="relative pt-32 pb-16 h-[50vh] min-h-[320px] w-full flex items-end overflow-hidden bg-on-surface">
        <div class="relative z-10 w-full px-6 max-w-7xl mx-auto pb-8">
            <p class="text-sm font-semibold text-white/80 uppercase tracking-widest mb-4">
                <a href="{{ url('/') }}" class="hover:text-secondary transition-colors">Beranda</a> <span class="mx-2">/</span> Tentang Kami
            </p>
            <h1 class="font-headline-xl text-headline-xl md:text-6xl text-white max-w-2xl leading-tight">Mengenal {{ $appName }} lebih dekat</h1>
        </div>
    </section>

    {{-- Siapa Kami --}}
    <section class="py-24 px-6 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-16 items-center">
            <div class="w-full md:w-[40%] relative">
                <div class="overflow-hidden shadow-md aspect-square rounded-lg bg-surface-container"></div>
            </div>
            <div class="w-full md:w-[60%]">
                <span class="text-sm font-bold text-secondary uppercase tracking-widest block mb-4">Tentang Kami</span>
                <p class="text-lg font-body-md text-body-md text-on-surface-variant mb-8 leading-relaxed">
                    Sebagai bagian dari <strong>PT Sinar Mas Elektrindo</strong>, {{ $appName }} hadir membawa komitmen kuat dalam menghadirkan solusi energi surya yang inovatif, efisien, dan andal. Kami memadukan kekuatan infrastruktur global dengan pemahaman mendalam tentang kebutuhan lokal Indonesia.
                </p>
                <div class="pl-8 border-l-4 border-secondary">
                    <p class="font-headline-lg text-headline-lg text-2xl text-primary leading-snug">
                        &ldquo;Misi kami bukan sekadar menjual panel, tetapi menjadi mitra transformasi energi yang memberdayakan masyarakat dan bisnis menuju masa depan yang lebih hijau.&rdquo;
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Visi --}}
    <section class="py-32 px-6 bg-white">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="font-headline-xl text-headline-xl md:text-5xl text-primary mb-6 leading-tight">
                Menjadi pelopor energi surya di Asia Tenggara yang paling dipercaya, mendorong masa depan di mana setiap bangunan mandiri energi dan berkelanjutan.
            </h2>
            <p class="text-sm font-bold text-outline uppercase tracking-[0.3em]">Visi Kami</p>
        </div>
    </section>

    {{-- Misi --}}
    @php
        $missions = [
            ['title' => 'Solusi Premium & Teruji', 'description' => 'Menyediakan panel surya dan inverter berkualitas terbaik yang telah teruji secara global untuk performa maksimal di iklim tropis.'],
            ['title' => 'Pemasangan Presisi', 'description' => 'Menjamin instalasi yang aman, rapi, dan efisien oleh tim teknisi bersertifikat yang memahami standar kelistrikan nasional.'],
            ['title' => 'Dukungan Purna Jual', 'description' => 'Memberikan ketenangan pikiran melalui pemeliharaan responsif dan garansi performa jangka panjang yang dapat diandalkan.'],
            ['title' => 'Edukasi Berkelanjutan', 'description' => 'Meningkatkan kesadaran masyarakat tentang manfaat dan pentingnya beralih ke energi bersih.'],
            ['title' => 'Inovasi Teknologi', 'description' => 'Terus mengadopsi teknologi terbaru dalam penyimpanan dan manajemen energi untuk efisiensi yang lebih baik.'],
        ];
    @endphp
    <section class="py-24 px-6 max-w-7xl mx-auto">
        <div class="mb-16">
            <span class="text-sm font-bold text-secondary uppercase tracking-widest block mb-4">Misi</span>
            <h2 class="font-headline-lg text-headline-lg md:text-5xl text-primary">Bagaimana kami mewujudkannya</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-24 gap-y-10">
            @foreach ($missions as $mission)
                <div class="flex gap-5">
                    <span class="material-symbols-outlined text-secondary shrink-0 text-3xl mt-0.5">check_circle</span>
                    <div>
                        <h4 class="font-headline-lg text-headline-lg text-xl text-primary mb-2">{{ $mission['title'] }}</h4>
                        <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">{{ $mission['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Nilai --}}
    @php
        $values = [
            ['icon' => 'savings', 'title' => 'Efisien & Terjangkau', 'description' => 'Menghadirkan solusi energi yang menekan biaya operasional jangka panjang.'],
            ['icon' => 'school', 'title' => 'Edukasi Masyarakat', 'description' => 'Memberikan pemahaman mendalam tentang transisi energi terbarukan.'],
            ['icon' => 'handshake', 'title' => 'Kolaborasi & Infrastruktur', 'description' => 'Membangun ekosistem bersama mitra strategis untuk jangkauan luas.'],
        ];
    @endphp
    <section class="py-24 px-6 max-w-7xl mx-auto mb-12">
        <div class="mb-16 text-center">
            <h2 class="font-headline-lg text-headline-lg md:text-5xl text-primary">Nilai-Nilai Kami</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-3 bg-primary p-10 flex flex-col justify-end rounded-lg">
                <div class="w-16 h-16 bg-secondary flex items-center justify-center mb-6 text-white rounded-lg">
                    <span class="material-symbols-outlined text-3xl">eco</span>
                </div>
                <h3 class="font-headline-lg text-headline-lg text-3xl text-white mb-4">Ekonomi Hijau &amp; Lapangan Kerja</h3>
                <p class="text-lg text-white/80 max-w-xl">Kami tidak hanya membangun infrastruktur energi, tetapi juga menggerakkan roda ekonomi hijau dengan menciptakan lapangan kerja baru bagi tenaga kerja lokal.</p>
            </div>

            @foreach ($values as $value)
                <div class="bg-white p-8 flex flex-col justify-center shadow-md border border-surface-container-low rounded-lg">
                    <div class="w-12 h-12 bg-surface-container-high flex items-center justify-center mb-5 text-primary rounded-lg">
                        <span class="material-symbols-outlined text-2xl">{{ $value['icon'] }}</span>
                    </div>
                    <h3 class="font-headline-lg text-headline-lg text-xl text-primary mb-3">{{ $value['title'] }}</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">{{ $value['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <x-sections.cta-band />
@endsection
