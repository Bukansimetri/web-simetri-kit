@extends('layouts.public')

@php
    $appName = app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name');

    $values = [
        ['icon' => 'lightbulb', 'title' => 'Inovasi Berkelanjutan', 'description' => 'Kami selalu mencari cara baru untuk memaksimalkan efisiensi energi surya dan meminimalkan dampak lingkungan.'],
        ['icon' => 'groups', 'title' => 'Kolaborasi Tim', 'description' => 'Lingkungan kerja yang inklusif di mana setiap ide didengar dan kolaborasi lintas disiplin didorong.'],
        ['icon' => 'public', 'title' => 'Dampak Nyata', 'description' => 'Pekerjaan Anda secara langsung berkontribusi pada pengurangan emisi karbon dan menciptakan masa depan yang lebih hijau.'],
    ];

    $process = [
        ['n' => '1', 'title' => 'Lamar', 'description' => 'Kirimkan CV dan portofolio Anda melalui portal karir kami.'],
        ['n' => '2', 'title' => 'Wawancara HR', 'description' => 'Sesi perkenalan untuk menilai kecocokan budaya dan pengalaman dasar.'],
        ['n' => '3', 'title' => 'Penilaian Teknis', 'description' => 'Wawancara mendalam dengan tim terkait atau studi kasus.'],
        ['n' => '4', 'title' => 'Penawaran', 'description' => 'Selamat datang di tim! Persiapan onboarding dimulai.'],
    ];
@endphp

@section('title', \App\Support\Seo\PageTitle::forStatic('karir', 'Karir'))
@section('meta_description', 'Bergabunglah dengan tim '.$appName.' dan jadi bagian dari transisi energi bersih Indonesia.')

@section('content')
    <x-sections.page-hero
        title="Gabung dengan Revolusi Energi Bersama {{ $appName }}"
        breadcrumb="Karir"
        subtitle="Kami mencari pemikir inovatif dan bersemangat untuk membangun masa depan yang berkelanjutan."
        :image="asset('images/mockup/home-3.jpg')"
    />

    {{-- Values --}}
    <section class="reveal-element px-6 max-w-7xl mx-auto py-20">
        <div class="text-center mb-12">
            <h2 class="font-headline-lg text-2xl md:text-3xl font-extrabold mb-2 text-primary-container">Mengapa Bergabung dengan Kami?</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Budaya kerja yang mendukung pertumbuhan dan inovasi Anda.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ($values as $value)
                <div class="bg-white rounded-xl p-8 border border-surface-container-low hover:border-primary-fixed-dim transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-white">{{ $value['icon'] }}</span>
                    </div>
                    <h3 class="font-headline-lg text-xl mb-3 text-on-surface">{{ $value['title'] }}</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">{{ $value['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Open Positions --}}
    <section id="positions" class="reveal-element px-6 max-w-5xl mx-auto py-12">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-extrabold text-on-surface mb-8">Posisi Terbuka</h2>

        @if ($jobOpenings->isEmpty())
            <div class="bg-surface-container-low rounded-lg p-8 text-center">
                <p class="text-on-surface-variant">Belum ada lowongan terbuka saat ini. Kirim CV Anda untuk masuk daftar tunggu kami.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($jobOpenings as $job)
                    <x-sections.job-card :job="$job" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Recruitment Process --}}
    <section class="reveal-element px-6 max-w-7xl mx-auto py-12 mb-12">
        <div class="bg-surface-container-low rounded-2xl p-8 md:p-12 border border-surface-container">
            <h2 class="font-headline-lg text-2xl md:text-3xl font-extrabold text-center mb-10 text-primary-container">Proses Rekrutmen</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                <div class="hidden md:block absolute top-6 left-[12.5%] right-[12.5%] h-0.5 bg-outline-variant z-0"></div>
                @foreach ($process as $step)
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-full bg-primary-container text-white flex items-center justify-center font-label-bold text-label-bold text-lg mb-4 shadow-sm">{{ $step['n'] }}</div>
                        <h4 class="font-headline-lg text-base font-bold mb-2 text-on-surface">{{ $step['title'] }}</h4>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $step['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <x-sections.cta-band
        title="Tidak menemukan posisi yang cocok?"
        subtitle="Kirimkan CV Anda — kami hubungi saat ada posisi sesuai"
        buttonLabel="Hubungi Kami"
        :buttonHref="url('/kontak')"
    />
@endsection
