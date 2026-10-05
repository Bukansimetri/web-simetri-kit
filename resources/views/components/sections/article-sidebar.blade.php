@props(['search' => '', 'popularTags' => collect(), 'activeTag' => null, 'activeCategory' => null])

<aside class="space-y-6 lg:sticky lg:top-28">
    <div class="bg-white border border-outline-variant/20 rounded-lg shadow-sm p-6">
        <h2 class="font-headline-lg text-headline-lg text-lg text-on-surface mb-4">Cari Artikel</h2>
        <form method="GET" action="{{ url('/artikel') }}" role="search" class="relative">
            @if ($activeCategory)
                <input type="hidden" name="kategori" value="{{ $activeCategory->id }}">
            @endif
            @if ($activeTag)
                <input type="hidden" name="tag" value="{{ $activeTag->slug }}">
            @endif
            <label for="artikel-search" class="sr-only">Cari artikel</label>
            <input id="artikel-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Ketik topik atau masalah..." class="w-full rounded-lg bg-surface-container border-0 pl-4 pr-12 py-3 text-sm text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container">
            <button type="submit" class="absolute inset-y-0 right-0 px-4 text-outline hover:text-primary transition-colors" aria-label="Cari">
                <span class="material-symbols-outlined">search</span>
            </button>
        </form>
    </div>

    @if ($popularTags->isNotEmpty())
        <div class="bg-white border border-outline-variant/20 rounded-lg shadow-sm p-6">
            <h2 class="font-headline-lg text-headline-lg text-lg text-on-surface mb-4">Tag Populer</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($popularTags as $tag)
                    @php $isActive = $activeTag && $activeTag->getKey() === $tag->getKey(); @endphp
                    <a href="{{ url('/artikel').'?'.http_build_query(array_filter(['q' => $search, 'kategori' => $activeCategory?->id, 'tag' => $tag->slug])) }}"
                       @class([
                           'px-3 py-1 rounded-full text-xs font-label-bold text-label-bold transition-colors',
                           'bg-primary-container text-white' => $isActive,
                           'bg-surface-variant text-on-surface-variant hover:bg-surface-container-high' => ! $isActive,
                       ])>{{ $tag->name }}</a>
                @endforeach
            </div>
            @if ($activeTag)
                <a href="{{ url('/artikel').'?'.http_build_query(array_filter(['q' => $search, 'kategori' => $activeCategory?->id])) }}" class="inline-block mt-4 text-sm font-semibold text-primary-container hover:underline">Hapus filter</a>
            @endif
        </div>
    @endif

    <x-sections.newsletter-card />
</aside>
