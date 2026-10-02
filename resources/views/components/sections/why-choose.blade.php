@php
    $content = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::WhyChoose);
@endphp
@if ($content)
<section class="reveal-element py-24 px-6 max-w-7xl mx-auto">
    <div class="mb-16 md:w-1/2">
        <h2 class="font-headline-xl text-3xl md:text-5xl font-extrabold tracking-tight mb-4 text-primary">{{ \App\Support\PageContent\PageContent::multiline($content->title) }}</h2>
        @if (filled($content->subtitle))
            <p class="text-base md:text-lg font-medium leading-relaxed text-secondary">{{ $content->subtitle }}</p>
        @endif
    </div>

    <div class="grid md:grid-cols-3 gap-8">
        @foreach ($content->items as $reason)
            <div @class([
                'p-8 rounded-lg transition-all duration-300',
                'bg-primary-container shadow-lg -translate-y-4 hover:-translate-y-6 hover:scale-105 rotate-1' => $reason->is_emphasized,
                'bg-white border border-gray-100 shadow-md hover:-translate-y-4 hover:shadow-lg' => ! $reason->is_emphasized,
            ])>
                <div @class([
                    'w-14 h-14 rounded-lg flex items-center justify-center mb-6',
                    'bg-white/30' => $reason->is_emphasized,
                    'bg-surface-container-low text-primary' => ! $reason->is_emphasized,
                ])>
                    <span @class(['material-symbols-outlined text-3xl', 'text-white' => $reason->is_emphasized])>{{ $reason->icon }}</span>
                </div>
                <h3 @class(['font-headline-lg text-xl mb-3', 'text-white' => $reason->is_emphasized, 'text-primary' => ! $reason->is_emphasized])>
                    {{ $reason->title }}
                </h3>
                <p @class(['leading-relaxed', 'text-white/90 font-medium' => $reason->is_emphasized, 'text-on-surface-variant' => ! $reason->is_emphasized])>
                    {{ $reason->description }}
                </p>
            </div>
        @endforeach
    </div>
</section>
@endif
