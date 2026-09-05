@props(['article'])

<a href="{{ url('/artikel/'.$article->slug) }}" class="bg-white border border-outline-variant/20 rounded-lg overflow-hidden hover:-translate-y-1 hover:shadow-md transition-all duration-300 flex flex-col h-full">
    <div class="aspect-video w-full bg-surface-container overflow-hidden">
        @if ($article->image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
        @else
            <div data-article-image-placeholder class="w-full h-full flex items-center justify-center text-outline">
                <span class="material-symbols-outlined text-4xl">image</span>
            </div>
        @endif
    </div>
    <div class="p-6 flex flex-col flex-1">
        <span class="text-[11px] font-label-bold text-label-bold uppercase tracking-wider text-primary mb-3">
            {{ $article->articleCategory->name }}
        </span>
        <h3 class="font-headline-lg text-headline-lg text-lg text-on-surface mb-2">{{ $article->title }}</h3>
        <p class="font-body-sm text-body-sm text-on-surface-variant mb-4 line-clamp-3">{{ $article->excerpt }}</p>
        <span class="text-xs text-outline mt-auto">{{ $article->published_at?->translatedFormat('d F Y') }}</span>
    </div>
</a>
