@extends('layouts.public')

@section('title', \App\Support\Seo\PageTitle::forContent('artikel_show', $article->meta_title, $article->title))
@section('meta_description', $article->seoDescription())
@section('og_title', $article->seoTitle())
@section('og_image', $article->seoImageUrl() ?? app(\App\Settings\SocialSettings::class)->ogImageUrl())

@section('content')
    <article class="pt-40 pb-16 px-6 max-w-3xl mx-auto">
        <nav aria-label="Breadcrumb" class="flex text-sm text-outline mb-6">
            <ol class="inline-flex items-center flex-wrap gap-y-1">
                <li><a class="hover:text-primary transition-colors" href="{{ url('/') }}">Beranda</a></li>
                <li class="flex items-center"><span class="material-symbols-outlined text-sm mx-1">chevron_right</span><a class="hover:text-primary transition-colors" href="{{ url('/artikel') }}">Artikel</a></li>
                <li class="flex items-center" aria-current="page"><span class="material-symbols-outlined text-sm mx-1">chevron_right</span><span class="text-primary font-medium">{{ $article->articleCategory->name }}</span></li>
            </ol>
        </nav>

        <span class="inline-block bg-surface-container-low text-primary px-3 py-1 rounded-full text-xs font-label-bold text-label-bold uppercase tracking-wider mb-4">{{ $article->articleCategory->name }}</span>
        <h1 class="font-headline-xl text-3xl md:text-5xl font-extrabold text-on-surface tracking-tight leading-tight mb-4">{{ $article->title }}</h1>
        <div class="flex items-center gap-4 text-sm text-outline mb-10">
            <span>{{ $article->published_at?->translatedFormat('d F Y') }}</span>
            <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
            <span class="flex items-center"><span class="material-symbols-outlined text-base mr-1">schedule</span> {{ $article->readingTimeMinutes() }} min baca</span>
            @if ($article->redaksi)
                <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
                <span>{{ $article->redaksi }}</span>
            @endif
        </div>

        <div class="mb-10">
            <x-layout.social-share :title="$article->title" :url="url()->current()" />
        </div>

        <div class="aspect-video w-full bg-surface-container rounded-lg mb-10 overflow-hidden">
            @if ($article->image_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) }}" alt="{{ $article->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
            @else
                <div data-article-image-placeholder class="w-full h-full flex items-center justify-center text-outline">
                    <span class="material-symbols-outlined text-6xl">image</span>
                </div>
            @endif
        </div>

        <div class="prose-content font-body-md text-body-md text-on-surface-variant leading-relaxed space-y-4">
            {!! $article->content !!}
        </div>

        @if ($article->tags->isNotEmpty())
            <div class="flex flex-wrap gap-2 mt-10 pt-8 border-t border-surface-container-low">
                @foreach ($article->tags as $tag)
                    <span class="bg-surface-variant text-on-surface-variant px-3 py-1 rounded-full text-xs font-label-bold text-label-bold">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif
    </article>

    @if ($related->isNotEmpty())
        <section class="reveal-element px-6 pb-24 max-w-7xl mx-auto">
            <h2 class="font-headline-lg text-2xl md:text-3xl text-primary mb-8">Artikel Terkait</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($related as $item)
                    <x-sections.article-card :article="$item" />
                @endforeach
            </div>
        </section>
    @endif

    <x-sections.cta-band
        title="Siap beralih ke energi surya?"
        subtitle="Konsultasi gratis dengan tim ahli kami"
        buttonLabel="Hubungi via WhatsApp"
    />
@endsection

@push('head')
    <x-seo.json-ld :schema="$schema" />
@endpush
