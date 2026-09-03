@props(['article'])

<a href="{{ url('/artikel/'.$article->slug) }}" class="bg-white border border-outline-variant/20 rounded-lg overflow-hidden hover:-translate-y-1 hover:shadow-md transition-all duration-300 flex flex-col h-full">
    <div class="aspect-video w-full bg-surface-container"></div>
    <div class="p-6 flex flex-col flex-1">
        <span class="text-[11px] font-label-bold text-label-bold uppercase tracking-wider text-primary mb-3">
            {{ str($article->category)->title() }}
        </span>
        <h3 class="font-headline-lg text-headline-lg text-lg text-on-surface mb-2">{{ $article->title }}</h3>
        <p class="font-body-sm text-body-sm text-on-surface-variant mb-4 line-clamp-3">{{ $article->excerpt }}</p>
        <span class="text-xs text-outline mt-auto">{{ $article->published_at?->translatedFormat('d F Y') }}</span>
    </div>
</a>
