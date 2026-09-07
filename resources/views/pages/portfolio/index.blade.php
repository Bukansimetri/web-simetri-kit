@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', 'Portfolio — '.$appName)
@section('meta_description', 'Proyek dan instalasi energi surya yang telah dikerjakan '.$appName.'.')

@section('content')
    <section class="pt-32 pb-12 px-6 max-w-7xl mx-auto text-center">
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">Portfolio</h1>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto">
            Proyek nyata yang telah kami kerjakan bersama klien &amp; mitra.
        </p>
    </section>

    <section class="px-6 max-w-7xl mx-auto pb-24">
        @if ($categories->isNotEmpty())
            <div class="flex flex-wrap justify-center gap-3 mb-12">
                <a href="{{ url('/portfolio') }}"
                   @class([
                       'px-4 py-2 rounded-full text-sm font-label-bold text-label-bold transition-colors',
                       'bg-primary text-white' => ! $activeSlug,
                       'bg-surface-variant text-on-surface-variant hover:bg-surface-container-high' => $activeSlug,
                   ])>Semua</a>
                @foreach ($categories as $category)
                    <a href="{{ url('/portfolio').'?kategori='.$category->slug }}"
                       @class([
                           'px-4 py-2 rounded-full text-sm font-label-bold text-label-bold transition-colors',
                           'bg-primary text-white' => $activeSlug === $category->slug,
                           'bg-surface-variant text-on-surface-variant hover:bg-surface-container-high' => $activeSlug !== $category->slug,
                       ])>{{ $category->name }}</a>
                @endforeach
            </div>
        @endif

        @forelse ($projects as $project)
            @if ($loop->first)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @endif
            <a href="{{ route('portfolio.show', $project) }}" class="group block rounded-lg overflow-hidden bg-surface-container-low border border-surface-container-low hover:-translate-y-1 transition-transform">
                <div class="aspect-video bg-surface-container overflow-hidden">
                    @if ($project->coverImageUrl())
                        <img src="{{ $project->coverImageUrl() }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-outline">
                            <span class="material-symbols-outlined text-5xl">image</span>
                        </div>
                    @endif
                </div>
                <div class="p-5">
                    <span class="text-xs font-label-bold text-label-bold text-secondary uppercase tracking-widest">{{ $project->portfolioCategory->name }}</span>
                    <h3 class="font-headline-lg text-headline-lg text-lg text-on-surface mt-1">{{ $project->title }}</h3>
                </div>
            </a>
            @if ($loop->last)
                </div>
            @endif
        @empty
            <p class="text-center text-on-surface-variant py-16">Belum ada proyek untuk ditampilkan saat ini.</p>
        @endforelse
    </section>
@endsection
