@extends('layouts.public')

@section('title', \App\Support\Seo\PageTitle::forStatic('produk_index', 'Katalog Produk'))
@section('meta_description', 'Temukan panel surya dan inverter yang tepat untuk proyek Anda, dari skala rumah tangga hingga industri besar.')

@php
    $hero = \App\Support\PageContent\PageContent::block(\App\Enums\PageBlockType::ProductsHero);
@endphp

@section('content')
    <x-sections.page-hero
        :title="$hero->value('title')"
        breadcrumb="Produk"
        :subtitle="$hero->value('subtitle')"
        :image="\App\Support\PageContent\PageContent::imageUrl($hero->value('image_path'), \App\Enums\PageBlockType::ProductsHero->defaultImagePath())"
    />

    <main class="reveal-element max-w-[1280px] mx-auto px-margin-mobile md:px-margin-desktop py-20">
        @if ($products->isEmpty())
            <p class="text-on-surface-variant">Produk belum tersedia saat ini.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
                @foreach ($products as $product)
                    <x-sections.product-card :product="$product" simple />
                @endforeach
            </div>
        @endif
    </main>

    {{-- CTA Kalkulator --}}
    @php
        $calculatorCta = \App\Support\PageContent\PageContent::cta(\App\Enums\CtaPlacement::ProductCalculator);
        $closingCta = \App\Support\PageContent\PageContent::cta(\App\Enums\CtaPlacement::ProductClosing);
    @endphp
    <section class="bg-primary-container py-20 px-margin-mobile md:px-margin-desktop my-12">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-white mb-6">{{ \App\Support\PageContent\PageContent::multiline($calculatorCta->title) }}</h2>
            @if (filled($calculatorCta->body))
                <p class="font-body-md text-body-md text-white/90 mb-10">{{ $calculatorCta->body }}</p>
            @endif
            <a href="{{ url('/#kalkulator') }}" class="inline-flex items-center justify-center bg-white text-primary-container px-8 py-4 font-label-bold text-label-bold font-bold hover:bg-surface transition-all hover:-translate-y-0.5 rounded-lg">
                {{ $calculatorCta->primary_label }} <span class="material-symbols-outlined ml-2">arrow_forward</span>
            </a>
        </div>
    </section>

    {{-- FAQ Seputar Produk --}}
    <x-sections.faq-list title="Pertanyaan Seputar Produk" :items="$productFaqs" />

    {{-- CTA Penutup --}}
    <section class="bg-primary-container text-on-primary py-20 px-margin-mobile md:px-margin-desktop">
        <div class="max-w-4xl mx-auto text-center flex flex-col items-center">
            <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-white mb-6">{{ \App\Support\PageContent\PageContent::multiline($closingCta->title) }}</h2>
            @if (filled($closingCta->body))
                <p class="font-body-md text-body-md text-white/90 mb-10">{{ $closingCta->body }}</p>
            @endif
            <a href="{{ url('/kontak') }}" class="px-8 py-4 font-label-bold text-label-bold font-bold hover:-translate-y-1 transition-all shadow-lg border border-white/20 bg-white text-primary rounded-lg inline-block">
                {{ $closingCta->primary_label }}
            </a>
        </div>
    </section>
@endsection
