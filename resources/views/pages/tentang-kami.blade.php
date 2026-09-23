@extends('layouts.public')

@php
    $appName = app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name');
    $about = app(\App\Settings\AboutPageSettings::class);

    $missions = $about->misiItems();
    $values = $about->nilaiItems();
    $trust = $about->trustItems();
@endphp

@section('title', 'Tentang Kami — '.$appName)
@section('meta_description', 'Mengenal '.$appName.' lebih dekat — visi, misi, dan nilai-nilai kami dalam menghadirkan solusi energi surya.')

@section('content')
    {{-- Page Hero --}}
    <section class="relative pt-32 pb-16 h-[50vh] min-h-[400px] w-full flex items-end overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="{{ $about->heroImageUrl() }}" alt="Solar array modern di atap gedung komersial" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-inverse-surface/90 via-inverse-surface/40 to-transparent"></div>
        </div>
        <div class="relative z-10 w-full px-6 max-w-7xl mx-auto pb-8">
            <p class="text-sm font-semibold text-white/70 uppercase tracking-widest mb-4">
                <a href="{{ url('/') }}" class="hover:text-primary-fixed transition-colors">Beranda</a>
                <span class="mx-2 text-white/40">/</span> Tentang Kami
            </p>
            <h1 class="font-headline-xl text-4xl md:text-6xl font-extrabold text-white max-w-4xl leading-tight tracking-tight">Mengenal {{ $appName }} Lebih Dekat</h1>
            <p class="mt-4 text-lg md:text-xl text-white/90 max-w-2xl leading-relaxed">{{ $about->hero_subtitle }}</p>
        </div>
    </section>

    {{-- Siapa Kami --}}
    <section class="reveal-element py-24 px-6 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-16 md:gap-24 items-center">
            <div class="w-full md:w-[40%] relative">
                <div class="overflow-hidden shadow-lg aspect-square rounded-lg">
                    <img src="{{ $about->siapaKamiImageUrl() }}" alt="Panel surya berkualitas tinggi memantulkan langit" loading="lazy" decoding="async" class="w-full h-full object-cover">
                </div>
                <div class="absolute -bottom-6 -right-6 md:-right-12 bg-white p-6 shadow-lg -rotate-3 max-w-[220px] border border-gray-100 rounded-lg">
                    <p class="font-semibold text-sm text-primary text-center">{{ $about->siapa_kami_badge_text }}</p>
                </div>
            </div>
            <div class="w-full md:w-[60%]">
                <span class="text-sm font-bold text-secondary uppercase tracking-widest block mb-4">{{ $about->siapa_kami_eyebrow }}</span>
                <h2 class="font-headline-lg text-3xl md:text-4xl font-extrabold text-primary leading-tight tracking-tight mb-6">{{ $about->siapa_kami_heading }}</h2>
                <div class="text-lg text-on-surface-variant mb-8 leading-relaxed [&_p]:mb-0">
                    {!! $about->sanitizedSiapaKamiBody($appName) !!}
                </div>
                <div class="pl-8 border-l-4 border-secondary">
                    <div class="font-headline-lg text-2xl md:text-3xl font-bold text-primary leading-snug [&_p]:mb-0">
                        &ldquo;{!! $about->sanitizedSiapaKamiQuote() !!}&rdquo;
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Visi --}}
    <section class="reveal-element py-32 px-6 bg-white relative overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[300px] md:text-[400px] text-surface-container-high/50 font-serif leading-none select-none z-0">&rdquo;</div>
        <div class="relative z-10 max-w-4xl mx-auto text-center">
            <p class="text-sm font-bold text-outline uppercase tracking-[0.3em] mb-4">{{ $about->visi_eyebrow }}</p>
            <h2 class="text-2xl md:text-4xl font-extrabold text-primary mb-4 leading-tight tracking-tight">
                {{ $about->visi_heading }}
            </h2>
            <p class="text-secondary font-medium text-base md:text-lg max-w-2xl mx-auto leading-relaxed">{{ $about->visi_subtext }}</p>
        </div>
    </section>

    {{-- Misi --}}
    <section class="reveal-element py-24 px-6 max-w-7xl mx-auto">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-sm font-bold text-secondary uppercase tracking-widest block mb-4">{{ $about->misi_eyebrow }}</span>
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3 text-primary">{{ $about->misi_heading }}</h2>
            <p class="text-secondary text-base md:text-lg font-medium max-w-2xl mx-auto leading-relaxed">{{ $about->misi_subtext }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-12 md:gap-24">
            <div class="md:col-span-3 space-y-10">
                @foreach (array_slice($missions, 0, 3) as $mission)
                    <div class="flex gap-5">
                        <span class="material-symbols-outlined text-secondary shrink-0 text-3xl mt-0.5">check_circle</span>
                        <div>
                            <h4 class="font-headline-lg text-xl font-bold text-primary mb-2">{{ $mission['title'] }}</h4>
                            <p class="text-on-surface-variant leading-relaxed">{{ $mission['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="md:col-span-2 space-y-10">
                @foreach (array_slice($missions, 3) as $mission)
                    <div class="flex gap-5">
                        <span class="material-symbols-outlined text-secondary shrink-0 text-3xl mt-0.5">check_circle</span>
                        <div>
                            <h4 class="font-headline-lg text-xl font-bold text-primary mb-2">{{ $mission['title'] }}</h4>
                            <p class="text-on-surface-variant leading-relaxed">{{ $mission['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Nilai (Bento Grid) --}}
    <section class="reveal-element py-24 px-6 max-w-7xl mx-auto mb-12">
        <div class="mb-12 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3 text-primary">{{ $about->nilai_heading }}</h2>
            <p class="text-secondary text-base md:text-lg font-medium max-w-2xl mx-auto leading-relaxed">{{ $about->nilai_subtext }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-8 items-stretch">
            <div class="md:col-span-3 relative rounded-lg overflow-hidden shadow-md min-h-[400px] flex flex-col justify-end p-8 md:p-12 group">
                <img src="{{ $about->nilaiFeaturedImageUrl() }}" alt="{{ $about->nilai_featured_title }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-inverse-surface/90 via-inverse-surface/50 to-transparent"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-primary-container text-white flex items-center justify-center mb-4 rounded-lg shadow-md">
                        <span class="material-symbols-outlined text-2xl">{{ $about->nilai_featured_icon }}</span>
                    </div>
                    <h3 class="font-headline-lg text-2xl md:text-3xl font-bold text-white mb-3 leading-snug">{{ $about->nilai_featured_title }}</h3>
                    <p class="text-white/90 text-sm md:text-base max-w-xl leading-relaxed">{{ $about->nilai_featured_description }}</p>
                </div>
            </div>
            <div class="md:col-span-2 flex flex-col gap-6 justify-between">
                @foreach ($values as $value)
                    <div class="bg-white p-6 md:p-8 rounded-lg shadow-md border border-surface-container-low transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-surface-container-high flex items-center justify-center text-primary shrink-0 rounded-lg">
                                <span class="material-symbols-outlined text-2xl">{{ $value['icon'] }}</span>
                            </div>
                            <div>
                                <h4 class="font-headline-lg text-lg font-bold text-primary mb-1.5">{{ $value['title'] }}</h4>
                                <p class="text-on-surface-variant text-sm leading-relaxed">{{ $value['description'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Trust Strip --}}
    <section class="reveal-element py-16 bg-white border-y border-surface-container-low">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 md:gap-0 divide-y md:divide-y-0 md:divide-x divide-outline-variant">
                @foreach ($trust as $stat)
                    <div class="w-full md:flex-1 text-center py-4 md:py-0">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-primary text-4xl mb-1">{{ $stat['icon'] }}</span>
                            <span class="font-headline-lg text-4xl font-extrabold text-primary tracking-tight">{{ $stat['value'] }}</span>
                            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ $stat['label'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <x-sections.team-members :members="$teamMembers" />

    <x-sections.testimonials :testimonials="$testimonials" />

    <x-sections.client-logos :logos="$clientLogos" />

    <x-sections.cta-band />
@endsection
