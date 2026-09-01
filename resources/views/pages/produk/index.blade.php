@extends('layouts.public')

@section('title', 'Katalog Produk — '.(app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name')))
@section('meta_description', 'Temukan panel surya dan inverter yang tepat untuk proyek Anda, dari skala rumah tangga hingga industri besar.')

@section('content')
    <section class="relative h-[40vh] md:h-[50vh] min-h-[320px] w-full flex items-end overflow-hidden bg-on-surface">
        <div class="relative z-10 w-full px-margin-mobile md:px-margin-desktop max-w-[1280px] mx-auto pb-12">
            <p class="font-label-bold text-label-bold text-white/80 uppercase tracking-widest mb-4">
                <a href="{{ url('/') }}" class="hover:text-secondary-fixed transition-colors">Beranda</a> <span class="mx-2">/</span> Produk
            </p>
            <h1 class="font-headline-xl text-headline-xl text-white max-w-2xl">Katalog Produk</h1>
        </div>
    </section>

    <main class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-desktop py-[80px]">
        <p class="font-body-md text-body-md text-on-surface-variant max-w-lg mb-12">
            Temukan panel surya dan inverter yang tepat untuk proyek Anda, dari skala rumah tangga hingga industri besar.
        </p>

        @if ($products->isEmpty())
            <p class="text-on-surface-variant">Produk belum tersedia saat ini.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
                @foreach ($products as $product)
                    <x-sections.product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </main>
@endsection
