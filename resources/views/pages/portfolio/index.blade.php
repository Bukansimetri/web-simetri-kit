@extends('layouts.public')

@php
    $appName = app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name');
    $hero = \App\Support\PageContent\PageContent::block(\App\Enums\PageBlockType::PortfolioHero);
@endphp

@section('title', \App\Support\Seo\PageTitle::forStatic('portfolio_index', 'Portfolio'))
@section('meta_description', 'Proyek dan instalasi energi surya yang telah dikerjakan '.$appName.'.')

@section('content')
    <x-sections.page-hero
        :title="$hero->value('title')"
        breadcrumb="Portofolio"
        :subtitle="$hero->value('subtitle')"
        :image="\App\Support\PageContent\PageContent::imageUrl($hero->value('image_path'), \App\Enums\PageBlockType::PortfolioHero->defaultImagePath())"
    />

    <section class="reveal-element px-6 max-w-7xl mx-auto py-16 pb-24">
        @if ($categories->isNotEmpty())
            <div class="flex flex-wrap justify-center gap-3 mb-12">
                <a href="{{ url('/portfolio') }}"
                   @class([
                       'px-5 py-2 rounded-full text-sm font-label-bold text-label-bold border transition-colors',
                       'bg-primary-container text-white border-primary-container' => ! $activeSlug,
                       'bg-white text-on-surface border-outline-variant hover:bg-surface-container' => $activeSlug,
                   ])>Semua</a>
                @foreach ($categories as $category)
                    <a href="{{ url('/portfolio').'?kategori='.$category->slug }}"
                       @class([
                           'px-5 py-2 rounded-full text-sm font-label-bold text-label-bold border transition-colors',
                           'bg-primary-container text-white border-primary-container' => $activeSlug === $category->slug,
                           'bg-white text-on-surface border-outline-variant hover:bg-surface-container' => $activeSlug !== $category->slug,
                       ])>{{ $category->name }}</a>
                @endforeach
            </div>
        @endif

        @forelse ($projects as $project)
            @if ($loop->first)
                <h2 class="sr-only">Daftar proyek</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @endif
            <x-sections.project-card :project="$project" />
            @if ($loop->last)
                </div>
            @endif
        @empty
            <p class="text-center text-on-surface-variant py-16">Belum ada proyek untuk ditampilkan saat ini.</p>
        @endforelse
    </section>
@endsection
