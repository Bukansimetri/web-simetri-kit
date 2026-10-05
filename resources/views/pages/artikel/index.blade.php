@extends('layouts.public')

@use('App\Enums\PublicSection')
@use('App\Support\PageContent\SectionVisibility')

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

    @php
        $baseQuery = array_filter([
            'q' => $search,
            'kategori' => $activeCategory?->id,
            'tag' => $activeTag?->slug,
        ]);
        $perPage = \App\Http\Controllers\Public\ArticleController::PER_PAGE;
        $categoryUrl = fn (?int $id) => url('/artikel').'?'.http_build_query(array_filter([
            'q' => $search,
            'kategori' => $id,
            'tag' => $activeTag?->slug,
        ]));
    @endphp

    <section class="reveal-element px-6 max-w-7xl mx-auto py-16">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-10 items-start">
            <div id="artikel">
                @if ($categories->isNotEmpty())
                    <div class="flex flex-wrap gap-3 mb-8">
                        <a href="{{ $categoryUrl(null) }}"
                           @class([
                               'px-5 py-2 font-label-bold text-label-bold rounded-lg transition-colors',
                               'bg-primary-container text-white shadow-sm' => ! $activeCategory,
                               'bg-surface-container hover:bg-surface-container-high text-on-surface' => $activeCategory,
                           ])>Semua</a>
                        @foreach ($categories as $category)
                            <a href="{{ $categoryUrl($category->id) }}"
                               @class([
                                   'px-5 py-2 font-label-bold text-label-bold rounded-lg transition-colors',
                                   'bg-primary-container text-white shadow-sm' => $activeCategory?->id === $category->id,
                                   'bg-surface-container hover:bg-surface-container-high text-on-surface' => $activeCategory?->id !== $category->id,
                               ])>{{ $category->name }}</a>
                        @endforeach
                    </div>
                @endif

                @if ($search !== '' || $activeTag)
                    <p class="text-sm text-on-surface-variant mb-6">
                        @if ($search !== '')
                            Hasil pencarian untuk &ldquo;<strong>{{ $search }}</strong>&rdquo;
                        @endif
                        @if ($activeTag)
                            Tag &ldquo;<strong>{{ $activeTag->name }}</strong>&rdquo;
                        @endif
                        &middot; <a href="{{ url('/artikel') }}" class="text-primary-container font-semibold hover:underline">Hapus pencarian</a>
                    </p>
                @endif

                @if ($articles->isEmpty())
                    <div class="py-12 text-on-surface-variant">
                        @if ($search !== '')
                            <p>Artikel dengan kata kunci &ldquo;{{ $search }}&rdquo; tidak ditemukan.</p>
                        @elseif ($activeTag || $activeCategory)
                            <p>Belum ada artikel untuk filter ini.</p>
                        @else
                            <p>Belum ada artikel yang dipublikasikan saat ini.</p>
                        @endif
                    </div>
                @else
                    <div id="artikel-grid" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach ($articles as $article)
                            <div id="artikel-{{ $loop->iteration }}" data-article-item>
                                <x-sections.article-card :article="$article" />
                            </div>
                        @endforeach
                    </div>

                    @if ($hasMore)
                        <div id="muat-lebih-banyak" class="mt-10 text-center"
                             x-data="{
                                 loading: false,
                                 async more(event) {
                                     const link = event.currentTarget;
                                     this.loading = true;
                                     try {
                                         const response = await fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                                         const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                                         const grid = document.getElementById('artikel-grid');
                                         const known = grid.querySelectorAll('[data-article-item]').length;
                                         doc.querySelectorAll('#artikel-grid [data-article-item]').forEach((item, index) => {
                                             if (index >= known) { grid.appendChild(document.importNode(item, true)); }
                                         });
                                         const next = doc.querySelector('#muat-lebih-banyak a');
                                         if (next) { link.href = next.href; } else { this.$root.remove(); }
                                     } catch (e) {
                                         window.location.href = link.href;
                                     } finally {
                                         this.loading = false;
                                     }
                                 },
                             }">
                            <a href="{{ url('/artikel').'?'.http_build_query([...$baseQuery, 'halaman' => $page + 1]) }}#artikel-{{ $perPage * $page + 1 }}"
                               @click.prevent="more($event)"
                               :class="loading && 'opacity-60 pointer-events-none'"
                               class="inline-flex items-center gap-2 border border-primary-container text-primary-container px-8 py-3 rounded-lg font-label-bold text-label-bold font-bold hover:bg-primary-container hover:text-white transition-colors">
                                <span x-show="! loading">Muat lebih banyak</span><span x-show="loading" x-cloak>Memuat...</span>
                                <span class="material-symbols-outlined">expand_more</span>
                            </a>
                        </div>
                    @endif
                @endif
            </div>

            <x-sections.article-sidebar :search="$search" :popular-tags="$popularTags" :active-tag="$activeTag" :active-category="$activeCategory" />
        </div>
    </section>

    @if (SectionVisibility::shows(PublicSection::ArticleCta))
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
                <a href="{{ url('/kontak') }}" class="btn-fill inline-flex items-center gap-2 bg-primary-container text-white px-8 py-4 rounded-lg font-label-bold text-label-bold font-bold hover:scale-105 transition-transform">
                    {{ $indexCta->primary_label }} <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>
    @endif

@endsection
