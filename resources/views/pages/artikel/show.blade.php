@extends('layouts.public')

@section('title', \App\Support\Seo\PageTitle::forContent('artikel_show', $article->meta_title, $article->title))
@section('meta_description', $article->seoDescription())
@section('og_title', $article->seoTitle())
@section('og_image', $article->seoImageUrl() ?? app(\App\Settings\SocialSettings::class)->ogImageUrl())

@section('content')
    @if ($isPreview)
        <div class="fixed top-16 inset-x-0 z-40 bg-amber-50 border-b border-amber-200 text-amber-800 text-sm px-4 py-2 text-center">Mode Preview (Admin) — halaman ini hanya terlihat oleh admin, bukan oleh pengunjung.</div>
    @endif

    <div class="pt-40 pb-16 px-6 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-12 items-start">
    <article class="min-w-0 max-w-3xl w-full">
        <nav aria-label="Breadcrumb" class="flex text-sm text-outline mb-6">
            <ol class="inline-flex items-center flex-wrap gap-y-1">
                <li><a class="hover:text-primary transition-colors" href="{{ url('/') }}">Beranda</a></li>
                <li class="flex items-center"><span class="material-symbols-outlined text-sm mx-1">chevron_right</span><a class="hover:text-primary transition-colors" href="{{ url('/artikel') }}">Artikel</a></li>
                <li class="flex items-center" aria-current="page"><span class="material-symbols-outlined text-sm mx-1">chevron_right</span><span class="text-primary font-medium">{{ $article->articleCategory->name }}</span></li>
            </ol>
        </nav>

        <span class="inline-block bg-surface-container-low text-primary px-3 py-1 rounded-full text-xs font-label-bold text-label-bold uppercase tracking-wider mb-4">{{ $article->articleCategory->name }}</span>
        <h1 class="font-headline-xl text-3xl md:text-5xl font-bold tracking-tight leading-tight text-on-surface mb-4">{{ $article->title }}</h1>
        <div class="flex items-center gap-4 text-sm text-outline mb-10">
            <span>{{ $article->published_at?->translatedFormat('d F Y') }}</span>
            <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
            <span class="flex items-center"><span class="material-symbols-outlined text-base mr-1">schedule</span> {{ $article->readingTimeMinutes() }} min baca</span>
            @if ($article->redaksi)
                <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
                <span>{{ $article->redaksi }}</span>
            @endif
            <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
            <span class="flex items-center"><span class="material-symbols-outlined text-base mr-1">visibility</span> {{ number_format($article->view_count, 0, ',', '.') }} kali dilihat</span>
        </div>

        <div class="mb-10">
            <x-layout.social-share :title="$article->title" :url="url()->current()" />
        </div>

        <figure class="mb-10">
            <div class="aspect-video w-full bg-surface-container rounded-lg overflow-hidden">
                @if ($article->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) }}" alt="{{ $article->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                @else
                    <div data-article-image-placeholder class="w-full h-full flex items-center justify-center text-outline">
                        <span class="material-symbols-outlined text-6xl">image</span>
                    </div>
                @endif
            </div>
            @if (filled($article->image_caption))
                <figcaption class="text-sm text-secondary mt-3 text-center">{{ $article->image_caption }}</figcaption>
            @endif
        </figure>

        <div class="rich-content font-body-md text-body-md text-on-surface-variant leading-relaxed space-y-4">
            {!! $article->content !!}
        </div>

        @if ($article->tags->isNotEmpty())
            <div class="flex flex-wrap gap-2 mt-10 pt-8 border-t border-surface-container-low">
                @foreach ($article->tags as $tag)
                    <span class="bg-surface-variant text-on-surface-variant px-3 py-1 rounded-full text-xs font-label-bold text-label-bold">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif

        @if ($article->relatedProducts->isNotEmpty())
            <section class="mt-12 pt-8 border-t border-surface-container-low">
                <h2 class="font-headline-xl text-3xl md:text-4xl font-bold tracking-tight leading-tight text-primary mb-6">Produk Terkait</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach ($article->relatedProducts->take(4) as $product)
                        <x-sections.product-card :product="$product" simple />
                    @endforeach
                </div>
            </section>
        @endif
    </article>

    <aside class="space-y-6 lg:sticky lg:top-28">
        <h2 class="sr-only">Artikel terbaru dan berlangganan</h2>
        @if ($latest->isNotEmpty())
            <div class="bg-white border border-outline-variant/20 rounded-lg shadow-sm p-6">
                <h3 class="font-headline-lg text-lg md:text-xl font-bold leading-snug text-on-surface mb-4">Artikel Terbaru</h3>
                <ul class="space-y-4">
                    @foreach ($latest as $item)
                        <li>
                            <a href="{{ url('/artikel/'.$item->slug) }}" class="group flex gap-3">
                                <span class="w-16 h-16 shrink-0 rounded-lg bg-surface-container overflow-hidden">
                                    @if ($item->image_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover">
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-2">{{ $item->title }}</span>
                                    <span class="block text-xs text-outline mt-1">{{ $item->published_at?->translatedFormat('d F Y') }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-sections.newsletter-card />
    </aside>
    </div>

    @if ($related->isNotEmpty())
        <section class="reveal-element px-6 pb-24 max-w-7xl mx-auto">
            <h2 class="font-headline-xl text-3xl md:text-4xl font-bold tracking-tight leading-tight text-primary mb-8">Artikel Terkait</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($related as $item)
                    <x-sections.article-card :article="$item" />
                @endforeach
            </div>
        </section>
    @endif

    <x-sections.cta-band :placement="\App\Enums\CtaPlacement::ArticleDetail" />
@endsection

@push('head')
    <x-seo.json-ld :schema="$schema" />
    @if ($isPreview)
        <meta name="robots" content="noindex, nofollow">
    @endif
@endpush
