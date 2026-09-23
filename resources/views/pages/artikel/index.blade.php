@extends('layouts.public')

@php
    $appName = app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name');
@endphp

@section('title', \App\Support\Seo\PageTitle::forStatic('artikel_index', 'Artikel & Blog'))
@section('meta_description', 'Tips, edukasi, dan berita seputar energi surya dari '.$appName.'.')

@section('content')
    {{-- Hero --}}
    <section class="relative px-6 pt-40 pb-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-surface-container to-background -z-10"></div>
        <div class="max-w-4xl mx-auto text-center">
            <nav class="flex justify-center text-sm text-outline mb-6">
                <ol class="flex items-center gap-2">
                    <li><a class="hover:text-primary transition-colors" href="{{ url('/') }}">Beranda</a></li>
                    <li class="flex items-center"><span class="material-symbols-outlined text-base">chevron_right</span></li>
                    <li class="text-primary font-semibold">Artikel</li>
                </ol>
            </nav>
            <h1 class="font-headline-xl text-4xl md:text-5xl font-extrabold mb-4 text-primary-container">Wawasan &amp; Edukasi Energi Surya</h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto">
                Temukan artikel terbaru seputar teknologi panel surya, tips hemat energi, dan panduan transisi ke energi terbarukan.
            </p>
        </div>
    </section>

    @if (! $featured && $articles->isEmpty())
        <section class="px-6 max-w-7xl mx-auto py-24">
            <p class="text-center text-on-surface-variant py-16">Belum ada artikel yang dipublikasikan saat ini.</p>
        </section>
    @else
        {{-- Featured --}}
        @if ($featured)
            <section class="reveal-element px-6 py-12 max-w-7xl mx-auto">
                <a href="{{ url('/artikel/'.$featured->slug) }}" class="block bg-surface-container-lowest border border-outline-variant/30 overflow-hidden group hover:shadow-lg transition-shadow duration-300 rounded-lg">
                    <div class="flex flex-col lg:flex-row">
                        <div class="lg:w-3/5 h-64 lg:h-auto relative overflow-hidden bg-surface-container">
                            @if ($featured->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($featured->image_path) }}" alt="{{ $featured->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-outline"><span class="material-symbols-outlined text-6xl">article</span></div>
                            @endif
                            <div class="absolute top-4 left-4">
                                <span class="bg-primary-container/90 backdrop-blur-md px-3 py-1 rounded-full text-xs font-label-bold text-label-bold text-white">{{ $featured->articleCategory->name }}</span>
                            </div>
                        </div>
                        <div class="lg:w-2/5 p-8 lg:p-12 flex flex-col justify-center">
                            <div class="flex items-center gap-4 text-sm text-outline mb-4">
                                <span>{{ $featured->published_at?->translatedFormat('d F Y') }}</span>
                                <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
                                <span class="flex items-center"><span class="material-symbols-outlined text-base mr-1">schedule</span> {{ $featured->readingTimeMinutes() }} min baca</span>
                            </div>
                            <h2 class="font-headline-lg text-2xl md:text-3xl text-on-surface mb-4 group-hover:text-primary transition-colors">{{ $featured->title }}</h2>
                            <p class="font-body-md text-body-md text-on-surface-variant mb-8 line-clamp-3">{{ $featured->excerpt }}</p>
                            <span class="inline-flex items-center font-label-bold text-label-bold text-primary group-hover:text-primary-container transition-colors w-fit">
                                Baca Selengkapnya <span class="material-symbols-outlined ml-2">arrow_forward</span>
                            </span>
                        </div>
                    </div>
                </a>
            </section>
        @endif

        {{-- Filter + Grid --}}
        <section class="reveal-element px-6 py-12 max-w-7xl mx-auto" x-data="{ cat: 'all' }">
            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap gap-3 mb-10">
                    <button type="button" @click="cat = 'all'"
                            :class="cat === 'all' ? 'bg-primary-container text-white shadow-sm' : 'bg-surface-container hover:bg-surface-container-high text-on-surface'"
                            class="px-5 py-2 font-label-bold text-label-bold rounded-lg transition-colors">Semua</button>
                    @foreach ($categories as $category)
                        <button type="button" @click="cat = '{{ $category->id }}'"
                                :class="cat === '{{ $category->id }}' ? 'bg-primary-container text-white shadow-sm' : 'bg-surface-container hover:bg-surface-container-high text-on-surface'"
                                class="px-5 py-2 font-label-bold text-label-bold rounded-lg transition-colors">{{ $category->name }}</button>
                    @endforeach
                </div>
            @endif

            @if ($articles->isEmpty())
                <p class="text-on-surface-variant py-8">Belum ada artikel lain.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($articles as $article)
                        <div x-show="cat === 'all' || cat === '{{ $article->article_category_id }}'">
                            <x-sections.article-card :article="$article" />
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Newsletter --}}
        <section class="reveal-element px-6 py-12 mt-12">
            <div class="max-w-4xl mx-auto bg-surface-container p-8 md:p-12 text-center relative overflow-hidden rounded-lg">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary-fixed rounded-full blur-3xl opacity-50 pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-tertiary-fixed rounded-full blur-3xl opacity-50 pointer-events-none"></div>
                <div class="relative z-10">
                    <span class="material-symbols-outlined text-5xl text-primary mb-4">forum</span>
                    <h2 class="font-headline-lg text-2xl md:text-3xl mb-4 text-primary">Punya pertanyaan seputar energi surya?</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-8 max-w-xl mx-auto">Tim kami siap membantu — dari pemilihan produk hingga estimasi penghematan untuk rumah atau bisnis Anda.</p>
                    <a href="{{ url('/kontak') }}" class="btn-fill inline-flex items-center gap-2 bg-primary-container text-white px-8 py-4 rounded-lg font-label-bold text-label-bold hover:scale-105 transition-transform">
                        Konsultasi Gratis <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection
