@extends('layouts.public')

@section('title', $product->name.' — '.(app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name')))
@section('meta_description', $product->seoDescription())
@section('og_title', $product->seoTitle())
@section('og_image', $product->seoImageUrl() ?? app(\App\Settings\SocialSettings::class)->ogImageUrl())

@section('content')
    <section class="px-margin-mobile md:px-margin-desktop pt-32 pb-12 max-w-[1280px] mx-auto">
        <nav aria-label="Breadcrumb" class="flex text-sm text-outline mb-8">
            <ol class="inline-flex items-center flex-wrap gap-y-1">
                <li><a class="hover:text-primary transition-colors" href="{{ url('/') }}">Beranda</a></li>
                <li class="flex items-center"><span class="material-symbols-outlined text-sm mx-1">chevron_right</span><a class="hover:text-primary transition-colors" href="{{ url('/produk') }}">Produk</a></li>
                <li class="flex items-center" aria-current="page"><span class="material-symbols-outlined text-sm mx-1">chevron_right</span><span class="text-primary font-medium">{{ $product->name }}</span></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-[80px] items-center">
            @php $images = $product->imageUrls(); @endphp
            @if (count($images) > 0)
                <div x-data="{ active: 0, images: {{ json_encode($images, JSON_UNESCAPED_SLASHES) }} }" class="flex flex-col gap-4">
                    <div class="relative bg-surface-container rounded-lg overflow-hidden h-[400px] md:h-[500px]">
                        <img :src="images[active]" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    </div>
                    @if (count($images) > 1)
                        <div class="flex gap-3 overflow-x-auto">
                            <template x-for="(image, index) in images" :key="index">
                                <button
                                    type="button"
                                    @click="active = index"
                                    :class="active === index ? 'border-primary' : 'border-transparent'"
                                    class="shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-colors"
                                >
                                    <img :src="image" class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    @endif
                </div>
            @else
                <div data-product-image-placeholder class="relative bg-surface-container rounded-lg overflow-hidden h-[400px] md:h-[500px] flex items-center justify-center text-outline">
                    <span class="material-symbols-outlined text-6xl">image</span>
                </div>
            @endif

            <div class="flex flex-col gap-[24px]">
                <h1 class="font-headline-xl text-headline-xl text-on-surface">{{ $product->name }}</h1>
                <div class="flex items-center gap-[12px]">
                    <span class="text-headline-lg font-headline-lg text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    @if ($product->strikethrough_price)
                        <span class="text-body-sm font-body-sm text-outline-variant line-through">Rp {{ number_format($product->strikethrough_price, 0, ',', '.') }}</span>
                    @endif
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant">{{ $product->description }}</p>
                <div class="flex flex-wrap gap-[12px] mt-[12px]">
                    <a href="{{ url('/kontak') }}" class="bg-primary-container text-white font-label-bold text-label-bold px-[48px] py-[12px] rounded-lg hover:shadow-md transition-all inline-flex items-center gap-[4px]">
                        <span class="material-symbols-outlined">forum</span> Konsultasi Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="reveal-element px-margin-mobile md:px-margin-desktop py-[80px] bg-surface-container-lowest">
        <div class="max-w-[1280px] mx-auto grid grid-cols-1 md:grid-cols-3 gap-[24px]">
            <div class="md:col-span-2 bg-surface border border-outline-variant/30 rounded-lg p-[48px]">
                <h2 class="font-headline-lg text-headline-lg mb-[24px] text-on-surface">Spesifikasi Teknis</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <tbody>
                            @foreach ($product->specs as $spec)
                                <tr class="border-b border-outline-variant/20">
                                    <th class="py-[12px] font-label-bold text-label-bold text-on-surface-variant w-1/3">{{ $spec['label'] }}</th>
                                    <td class="py-[12px] font-body-md text-body-md text-on-surface">{{ $spec['value'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-[24px]">
                @foreach ($product->features as $feature)
                    <div class="bg-surface-container-low border border-outline-variant/20 rounded-lg p-[24px] flex items-start gap-[24px]">
                        <div class="bg-primary/10 p-[12px] rounded-full text-primary">
                            <span class="material-symbols-outlined">{{ $feature['icon'] }}</span>
                        </div>
                        <div>
                            <h3 class="font-label-bold text-label-bold text-on-surface mb-[4px]">{{ $feature['title'] }}</h3>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $feature['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Masa Depan Energi Anda --}}
    <section class="reveal-element px-margin-mobile md:px-margin-desktop py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-primary/5 -z-10"></div>
        <div class="max-w-[1280px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
            <div class="flex flex-col gap-6">
                <h2 class="font-headline-xl text-3xl md:text-4xl font-extrabold text-primary tracking-tight">Masa Depan Energi Anda</h2>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                    Berinvestasi pada {{ Str::lower($product->name) }} bukan sekadar mengurangi tagihan listrik, tetapi juga bentuk komitmen terhadap kelestarian bumi — dirancang untuk integrasi mulus dengan arsitektur modern.
                </p>
                <a href="{{ url('/kontak') }}" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-primary-container transition-colors w-fit">
                    Konsultasi kebutuhan Anda <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </a>
            </div>
            <div class="h-[300px] rounded-lg overflow-hidden bg-surface-container">
                @if (count($product->imageUrls()) > 0)
                    <img src="{{ $product->coverImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-outline"><span class="material-symbols-outlined text-6xl">solar_power</span></div>
                @endif
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="reveal-element px-margin-mobile md:px-margin-desktop py-[80px] max-w-[1280px] mx-auto">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-8">Produk Terkait</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
                @foreach ($relatedProducts as $related)
                    <x-sections.product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('head')
    <x-seo.json-ld :schema="$schema" />
@endpush
