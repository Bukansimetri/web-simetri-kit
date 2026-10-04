@extends('layouts.public')

@php
    $appName = app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name');
    $hero = \App\Support\PageContent\PageContent::block(\App\Enums\PageBlockType::ArticlesHero);
@endphp

@section('title', \App\Support\Seo\PageTitle::forStatic('artikel_index', 'Artikel & Blog'))
@section('meta_description', 'Tips, edukasi, dan berita seputar energi surya dari '.$appName.'.')

@section('content')
    <x-sections.page-hero
        :title="$hero->value('title')"
        breadcrumb="Artikel"
        :subtitle="$hero->value('subtitle')"
        :image="\App\Support\PageContent\PageContent::imageUrl($hero->value('image_path'), \App\Enums\PageBlockType::ArticlesHero->defaultImagePath())"
    />

    <section class="reveal-element px-6 max-w-7xl mx-auto py-16" x-data="{ cat: 'all' }">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-10 items-start">
            <div>
                @if ($categories->isNotEmpty())
                    <div class="flex flex-wrap gap-3 mb-8">
                        <button type="button" @click="cat = 'all'"
                                :class="cat === 'all' ? 'bg-primary-container text-white shadow-sm' : 'bg-surface-container hover:bg-surface-container-high text-on-surface'"
                                class="px-5 py-2 font-label-bold text-label-bold rounded-lg transition-colors">Semua</button>
                        @foreach ($categories as $category)
                            <button type="button" @click="cat = '{{ $category->id }}'"
                                    :class="cat === '{{ $category->id }}' ? 'bg-primary-container text-white shadow-sm' : 'bg-surface-container hover:bg-surface-container-high text-on-surface'"
                                    class="px-5 py-2 font-label-bold text-label-bold rounded-lg transition-colors">{{ $category->name }}</button>
                        @endforeach
                    </div>
                @endif

                @if ($search !== '')
                    <p class="text-sm text-on-surface-variant mb-6">
                        Hasil pencarian untuk &ldquo;<strong>{{ $search }}</strong>&rdquo;
                        &middot; <a href="{{ url('/artikel') }}" class="text-primary-container font-semibold hover:underline">Hapus pencarian</a>
                    </p>
                @endif

                @if ($articles->isEmpty())
                    <div class="py-12 text-on-surface-variant">
                        @if ($search !== '')
                            <p>Artikel dengan kata kunci &ldquo;{{ $search }}&rdquo; tidak ditemukan.</p>
                        @else
                            <p>Belum ada artikel yang dipublikasikan saat ini.</p>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach ($articles as $article)
                            <div x-show="cat === 'all' || cat === '{{ $article->article_category_id }}'">
                                <x-sections.article-card :article="$article" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <x-sections.article-sidebar :search="$search" />
        </div>
    </section>

    {{-- CTA --}}
    @php
        $indexCta = \App\Support\PageContent\PageContent::cta(\App\Enums\CtaPlacement::ArticleIndex);
    @endphp
    <section class="reveal-element px-6 py-12 mt-12">
        <div class="max-w-4xl mx-auto bg-surface-container p-8 md:p-12 text-center relative overflow-hidden rounded-lg">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary-fixed rounded-full blur-3xl opacity-50 pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-tertiary-fixed rounded-full blur-3xl opacity-50 pointer-events-none"></div>
            <div class="relative z-10">
                <span class="material-symbols-outlined text-5xl text-primary mb-4">forum</span>
                <h2 class="font-headline-lg text-2xl md:text-3xl mb-4 text-primary">{{ \App\Support\PageContent\PageContent::multiline($indexCta->title) }}</h2>
                @if (filled($indexCta->body))
                    <p class="font-body-md text-body-md text-on-surface-variant mb-8 max-w-xl mx-auto">{{ $indexCta->body }}</p>
                @endif
                <a href="{{ url('/kontak') }}" class="btn-fill inline-flex items-center gap-2 bg-primary-container text-white px-8 py-4 rounded-lg font-label-bold text-label-bold hover:scale-105 transition-transform">
                    {{ $indexCta->primary_label }} <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>
@endsection
