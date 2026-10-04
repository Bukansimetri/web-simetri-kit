@props(['project'])

@php
    $summary = $project->summary();
@endphp

<a href="{{ route('portfolio.show', $project) }}" class="group bg-white border border-outline-variant/20 rounded-lg overflow-hidden shadow-sm hover:-translate-y-1 hover:shadow-md transition-all duration-300 flex flex-col h-full">
    <div class="aspect-video w-full bg-surface-container overflow-hidden">
        @if ($project->coverImageUrl())
            <img src="{{ $project->coverImageUrl() }}" alt="{{ $project->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            <div data-project-image-placeholder class="w-full h-full flex items-center justify-center text-outline">
                <span class="material-symbols-outlined text-5xl">image</span>
            </div>
        @endif
    </div>
    <div class="p-6 flex flex-col flex-1">
        <span class="text-[11px] font-label-bold text-label-bold uppercase tracking-wider text-primary-container mb-2">{{ $project->portfolioCategory->name }}</span>
        <h3 class="font-headline-lg text-headline-lg text-lg text-on-surface mb-2">{{ $project->title }}</h3>
        @if ($summary !== '')
            <p class="font-body-sm text-body-sm text-on-surface-variant mb-4 line-clamp-3">{{ $summary }}</p>
        @endif
        <span class="mt-auto pt-4 border-t border-surface-container-low inline-flex items-center gap-1 text-sm font-semibold text-primary-container group-hover:text-primary transition-colors">
            Lihat Detail Proyek <span class="material-symbols-outlined text-base">arrow_forward</span>
        </span>
    </div>
</a>
