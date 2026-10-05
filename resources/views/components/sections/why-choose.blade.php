@php
    $content = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::WhyChoose);
@endphp
@if ($content)
<section class="reveal-element py-24 px-6 max-w-7xl mx-auto">
    <div class="mb-16 text-center max-w-3xl mx-auto">
        <h2 class="font-headline-xl text-3xl md:text-5xl font-bold tracking-tight mb-4 text-primary">{{ \App\Support\PageContent\PageContent::multiline($content->title) }}</h2>
        @if (filled($content->subtitle))
            <p class="text-base md:text-lg font-medium leading-relaxed text-secondary">{{ $content->subtitle }}</p>
        @endif
    </div>

    <div class="grid md:grid-cols-3 gap-8 items-stretch">
        @foreach ($content->items as $reason)
            <div @class([
                'p-8 rounded-lg bg-white h-full transition-shadow duration-300 hover:shadow-lg',
                'border-2 border-primary-container shadow-md' => $reason->is_emphasized,
                'border border-gray-100 shadow-sm' => ! $reason->is_emphasized,
            ])>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-6 bg-surface-container-low text-primary">
                    <span class="material-symbols-outlined text-xl">{{ $reason->icon }}</span>
                </div>
                <h3 class="font-headline-lg text-lg mb-3 text-primary">
                    {{ $reason->title }}
                </h3>
                <p class="text-sm leading-relaxed text-on-surface-variant">
                    {{ $reason->description }}
                </p>
            </div>
        @endforeach
    </div>
</section>
@endif
