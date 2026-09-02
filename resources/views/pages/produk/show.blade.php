@extends('layouts.public')

@section('title', $product->name.' — '.(app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name')))
@section('meta_description', $product->short_description)

@section('content')
    <section class="px-margin-mobile md:px-margin-desktop pt-32 pb-12 max-w-[1280px] mx-auto">
        <p class="font-label-bold text-label-bold text-on-surface-variant uppercase tracking-widest mb-6">
            <a href="{{ url('/') }}" class="hover:text-primary">Beranda</a>
            <span class="mx-2">/</span>
            <a href="{{ url('/produk') }}" class="hover:text-primary">Produk</a>
            <span class="mx-2">/</span>
            {{ $product->name }}
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-[80px] items-center">
            <div class="relative bg-surface-container rounded-lg overflow-hidden h-[400px] md:h-[500px]"></div>

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

    <section class="px-margin-mobile md:px-margin-desktop py-[80px] bg-surface-container-lowest">
        <div class="max-w-[1280px] mx-auto grid grid-cols-1 md:grid-cols-3 gap-[24px]">
            <div class="md:col-span-2 bg-surface border border-outline-variant/30 rounded-lg p-[48px]">
                <h2 class="font-headline-lg text-headline-lg mb-[24px] text-on-surface">Spesifikasi Teknis</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <tbody>
                            @foreach ($product->specs as $label => $value)
                                <tr class="border-b border-outline-variant/20">
                                    <th class="py-[12px] font-label-bold text-label-bold text-on-surface-variant w-1/3">{{ $label }}</th>
                                    <td class="py-[12px] font-body-md text-body-md text-on-surface">{{ $value }}</td>
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

    @if ($relatedProducts->isNotEmpty())
        <section class="px-margin-mobile md:px-margin-desktop py-[80px] max-w-[1280px] mx-auto">
            <h2 class="font-headline-lg text-headline-lg text-primary mb-8">Produk Terkait</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
                @foreach ($relatedProducts as $related)
                    <x-sections.product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
