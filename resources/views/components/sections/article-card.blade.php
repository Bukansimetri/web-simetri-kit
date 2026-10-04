@props(['article'])

<a href="{{ url('/artikel/'.$article->slug) }}" class="group bg-white border border-outline-variant/20 rounded-lg overflow-hidden hover:-translate-y-1 hover:shadow-md transition-all duration-300 flex flex-col h-full">
    <div class="relative aspect-video w-full bg-surface-container overflow-hidden">
        @if ($article->image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) }}" alt="{{ $article->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            <div data-article-image-placeholder class="w-full h-full flex items-center justify-center text-outline">
                <span class="material-symbols-outlined text-4xl">image</span>
            </div>
        @endif
        <span class="absolute top-3 left-3 bg-primary-container text-white px-3 py-1 rounded-full text-[11px] font-label-bold text-label-bold uppercase tracking-wider shadow-sm">
            {{ $article->articleCategory->name }}
        </span>
    </div>
    <div class="p-6 flex flex-col flex-1">
        <span class="text-xs text-outline mb-2">{{ $article->published_at?->translatedFormat('d F Y') }}</span>
        <h3 class="font-headline-lg text-headline-lg text-lg text-on-surface mb-2 line-clamp-3">{{ $article->title }}</h3>
        <p class="font-body-sm text-body-sm text-on-surface-variant mb-4 line-clamp-3">{{ $article->excerpt }}</p>
        <span class="mt-auto inline-flex items-center gap-1 text-sm font-semibold text-primary-container group-hover:text-primary transition-colors">
            Baca Selengkapnya <span class="material-symbols-outlined text-base">arrow_forward</span>
        </span>
    </div>
</a>
