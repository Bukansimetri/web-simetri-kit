@php
    $content = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::HowItWorks);
    $rotations = ['-rotate-1', 'rotate-2', '-rotate-2', 'rotate-1'];
    $offsets = ['md:mt-0', 'md:mt-12', 'md:mt-4', 'md:mt-16'];
@endphp
@if ($content)
<section class="reveal-element py-24 px-6 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-20">
            <h2 class="font-headline-xl text-3xl md:text-5xl font-extrabold tracking-tight mb-4 text-primary">{{ \App\Support\PageContent\PageContent::multiline($content->title) }}</h2>
            @if (filled($content->subtitle))
                <p class="text-base md:text-lg font-medium max-w-2xl mx-auto text-secondary">{{ $content->subtitle }}</p>
            @endif
        </div>

        <div class="relative flex flex-col md:flex-row justify-between items-center md:items-start gap-16 md:gap-4">
            <div class="hidden md:block absolute top-12 left-10 right-10 h-0.5 border-t-2 border-dashed border-primary/20 -z-10"></div>

            @foreach ($content->items as $step)
                <div class="w-full md:w-1/4 relative group {{ $offsets[$loop->index] ?? 'md:mt-0' }} transition-transform duration-300 hover:-translate-y-2">
                    <div @class([
                        'w-20 h-20 rounded-full border-4 border-surface shadow-sm flex items-center justify-center font-headline-lg text-2xl mx-auto mb-6 group-hover:scale-110 group-hover:shadow-md transition-all z-10 relative',
                        'text-white bg-primary-container' => $step->is_emphasized,
                        'bg-white text-primary group-hover:border-primary-container' => ! $step->is_emphasized,
                    ])>{{ \App\Enums\PageSection::HowItWorks->stepNumber($loop->iteration) }}</div>
                    <div class="bg-white p-6 shadow-md text-center {{ $rotations[$loop->index] ?? '' }} group-hover:shadow-lg transition-shadow rounded-lg">
                        <h4 class="font-bold text-lg text-primary mb-2">{{ $step->title }}</h4>
                        <p class="text-sm text-on-surface-variant leading-relaxed">{{ $step->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
