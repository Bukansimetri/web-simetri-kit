@php
    $content = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::HowItWorks);
@endphp
@if ($content)
<section class="reveal-element py-24 px-6 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-20">
            <h2 class="font-headline-xl text-3xl md:text-4xl font-bold tracking-tight leading-tight mb-4 text-primary">{{ \App\Support\PageContent\PageContent::multiline($content->title) }}</h2>
            @if (filled($content->subtitle))
                <p class="text-base md:text-lg font-medium max-w-2xl mx-auto text-secondary">{{ $content->subtitle }}</p>
            @endif
        </div>

        <div class="relative flex flex-col md:flex-row justify-between items-center md:items-start gap-16 md:gap-4">
            <div class="hidden md:block absolute top-12 left-10 right-10 h-0.5 border-t-2 border-dashed border-primary/20 -z-10"></div>

            @foreach ($content->items as $step)
                <div class="w-full md:w-1/4 relative group flex flex-col self-stretch">
                    <div class="w-20 h-20 rounded-full border-4 border-surface shadow-sm flex items-center justify-center font-headline-lg text-2xl mx-auto mb-6 bg-white text-primary group-hover:bg-primary-container group-hover:text-white group-hover:scale-110 group-hover:shadow-md transition-all z-10 relative">{{ \App\Enums\PageSection::HowItWorks->stepNumber($loop->iteration) }}</div>
                    <div class="bg-white p-6 shadow-md text-center flex-1 group-hover:shadow-lg transition-shadow rounded-lg">
                        <h3 class="font-headline-lg text-lg md:text-xl font-bold leading-snug text-primary mb-2">{{ $step->title }}</h3>
                        <p class="text-sm text-on-surface-variant leading-relaxed">{{ $step->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
