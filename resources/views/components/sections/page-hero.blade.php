@props([
    'title',
    'subtitle' => null,
    'breadcrumb' => null,
    'image' => null,
])

@php
    $heroImage = $image ?: asset('images/mockup/produk-1.jpg');
@endphp

<section class="relative pt-32 pb-16 h-[45vh] md:h-[50vh] min-h-[380px] w-full flex items-end overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="{{ $heroImage }}" alt="" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-inverse-surface/90 via-inverse-surface/40 to-transparent"></div>
    </div>
    <div class="relative z-10 w-full px-6 max-w-7xl mx-auto pb-8">
        @if ($breadcrumb)
            <p class="text-sm font-semibold text-white/70 uppercase tracking-widest mb-4">
                <a href="{{ url('/') }}" class="hover:text-primary-fixed transition-colors">Beranda</a>
                <span class="mx-2 text-white/40">/</span> {{ $breadcrumb }}
            </p>
        @endif
        <h1 class="font-headline-xl text-4xl md:text-6xl font-extrabold text-white max-w-4xl leading-tight tracking-tight">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-4 text-lg md:text-xl text-white/90 max-w-2xl leading-relaxed">{{ $subtitle }}</p>
        @endif
    </div>
</section>
