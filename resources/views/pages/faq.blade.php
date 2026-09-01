@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', 'FAQ — '.$appName)
@section('meta_description', 'Pertanyaan yang sering diajukan seputar produk dan layanan '.$appName.'.')

@section('content')
    <section class="pt-32 pb-16 px-6 max-w-7xl mx-auto text-center">
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">Pertanyaan Umum</h1>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto">
            Temukan jawaban atas pertanyaan yang paling sering diajukan seputar produk dan layanan kami.
        </p>
    </section>

    <section class="px-6 max-w-3xl mx-auto pb-24">
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
