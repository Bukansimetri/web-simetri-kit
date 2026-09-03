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

    <main class="max-w-[1280px] mx-auto px-margin-mobile md:px-margin-desktop py-20">
        <p class="font-body-md text-body-md text-on-surface-variant max-w-lg mb-12">
            Temukan panel surya dan inverter yang tepat untuk proyek Anda, dari skala rumah tangga hingga industri besar.
        </p>

        @if ($products->isEmpty())
            <p class="text-on-surface-variant">Produk belum tersedia saat ini.</p>
        @else
            <div x-data="{ activeCategory: 'all' }">
                @if ($categories->isNotEmpty())
                    <div class="flex flex-wrap gap-3 mb-10">
                        <button
                            type="button"
                            @click="activeCategory = 'all'"
                            :class="activeCategory === 'all' ? 'bg-primary text-white' : 'bg-transparent border border-outline text-on-surface hover:bg-surface-variant'"
                            class="px-5 py-2 font-label-bold text-label-bold rounded-lg transition-colors"
                        >
                            Semua
                        </button>
                        @foreach ($categories as $category)
                            <button
                                type="button"
                                @click="activeCategory = '{{ $category->id }}'"
                                :class="activeCategory === '{{ $category->id }}' ? 'bg-primary text-white' : 'bg-transparent border border-outline text-on-surface hover:bg-surface-variant'"
                                class="px-5 py-2 font-label-bold text-label-bold rounded-lg transition-colors"
                            >
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
                    @foreach ($products as $product)
                        <div x-show="activeCategory === 'all' || activeCategory === '{{ $product->category_id }}'">
                            <x-sections.product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </main>

    {{-- CTA Kalkulator --}}
    <section class="bg-primary-container py-20 px-margin-mobile md:px-margin-desktop my-12">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-white mb-6">Bingung pilih yang mana?</h2>
            <p class="font-body-md text-body-md text-white/90 mb-10">Gunakan kalkulator kami untuk memperkirakan kebutuhan daya dan potensi penghematan bulanan Anda.</p>
            <a href="{{ url('/#kalkulator') }}" class="inline-flex items-center justify-center bg-white text-primary-container px-8 py-4 font-label-bold text-label-bold hover:bg-surface transition-all hover:-translate-y-0.5 rounded-lg">
                Coba kalkulator hemat listrik <span class="material-symbols-outlined ml-2">arrow_forward</span>
            </a>
        </div>
    </section>

    {{-- FAQ Seputar Produk --}}
    <section class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-20">
        <h2 class="font-headline-lg text-headline-lg text-primary-container mb-12 text-center">Pertanyaan Seputar Produk</h2>
        <div class="space-y-4">
            @php
                $productFaqs = [
                    ['question' => 'Berapa lama garansi panel?', 'answer' => 'Panel surya SUOER dilengkapi dengan garansi kinerja linier hingga 25 tahun, memastikan efisiensi panel tidak akan turun di bawah 80% dalam kurun waktu tersebut. Inverter biasanya memiliki garansi standar 5 hingga 10 tahun tergantung model.'],
                    ['question' => 'Apakah bisa custom kapasitas?', 'answer' => 'Sangat bisa. Kami merancang sistem berdasarkan kebutuhan beban listrik spesifik dan luas atap yang tersedia. Tim teknisi kami akan melakukan survey untuk merancang kapasitas yang paling optimal.'],
                    ['question' => 'Bagaimana proses instalasinya?', 'answer' => 'Proses dimulai dari survey lokasi, perancangan sistem, pengajuan izin (jika on-grid), instalasi fisik oleh teknisi bersertifikat kami, hingga tahap commissioning dan serah terima pengoperasian sistem kepada Anda.'],
                ];
            @endphp
            @foreach ($productFaqs as $index => $faq)
                <details class="group bg-surface-container-lowest shadow-md border border-surface-container-high overflow-hidden rounded-lg" @if ($index === 0) open @endif>
                    <summary class="flex justify-between items-center gap-4 font-headline-lg text-headline-lg text-xl text-primary-container cursor-pointer p-6 list-none hover:bg-surface transition-colors">
                        <span>{{ $faq['question'] }}</span>
                        <span class="material-symbols-outlined transition-transform group-open:rotate-180 shrink-0">expand_more</span>
                    </summary>
                    <div class="px-6 pb-6 pt-2 font-body-md text-body-md text-on-surface-variant">
                        {{ $faq['answer'] }}
                    </div>
                </details>
            @endforeach
        </div>
    </section>

    {{-- CTA Penutup --}}
    <section class="bg-primary-container text-on-primary py-20 px-margin-mobile md:px-margin-desktop">
        <div class="max-w-4xl mx-auto text-center flex flex-col items-center">
            <h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-white mb-6">Belum yakin kapasitas yang Anda butuhkan?</h2>
            <p class="font-body-md text-body-md text-white/90 mb-10">Konsultasi gratis dengan tim teknis ahli kami untuk mendapatkan perhitungan yang akurat dan solusi yang tepat.</p>
            <a href="{{ url('/kontak') }}" class="px-8 py-4 font-label-bold text-label-bold font-bold hover:-translate-y-1 transition-all shadow-lg border border-white/20 bg-white text-primary rounded-lg inline-block">
                Konsultasi gratis dengan tim SUOER
            </a>
        </div>
    </section>
@endsection
