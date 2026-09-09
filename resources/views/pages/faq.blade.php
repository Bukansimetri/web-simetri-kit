@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', 'FAQ — '.$appName)
@section('meta_description', 'Pertanyaan yang sering diajukan seputar produk dan layanan '.$appName.'.')

@section('content')
    <x-sections.page-hero
        title="Pertanyaan Umum"
        breadcrumb="FAQ"
        subtitle="Temukan jawaban atas pertanyaan yang paling sering diajukan seputar produk dan layanan kami."
        :image="asset('images/mockup/produk-detail-2.jpg')"
    />

    <section class="reveal-element px-6 max-w-3xl mx-auto py-24">
        @if ($faqItems->isEmpty())
            <p class="text-center text-on-surface-variant">Belum ada pertanyaan yang tersedia saat ini.</p>
        @else
            <x-sections.faq-accordion :items="$faqItems" />
        @endif
    </section>

    <x-sections.cta-band
        title="Masih ada pertanyaan lain?"
        description="Tim kami siap membantu menjawab pertanyaan spesifik seputar kebutuhan energi surya Anda."
    />
@endsection
