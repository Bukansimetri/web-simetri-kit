@props(['title', 'items', 'subtitle' => null, 'band' => false])

@if ($items->isNotEmpty())
    <section @class([
        'reveal-element',
        'px-margin-mobile md:px-margin-desktop py-20' => ! $band,
        'bg-surface-container-lowest border-t border-surface-container-high px-6 py-20' => $band,
    ])>
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="font-headline-lg text-headline-lg text-primary-container">{{ $title }}</h2>
                @if (filled($subtitle))
                    <p class="font-body-md text-body-md text-on-surface-variant mt-4">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="space-y-4">
                @foreach ($items as $index => $faq)
                    <details class="group bg-surface-container-lowest shadow-md border border-surface-container-high overflow-hidden rounded-lg" @if ($index === 0) open @endif>
                        <summary class="flex justify-between items-center gap-4 font-headline-lg text-headline-lg text-xl text-primary-container cursor-pointer p-6 list-none hover:bg-surface transition-colors">
                            <span>{{ $faq->question }}</span>
                            <span class="material-symbols-outlined transition-transform group-open:rotate-180 shrink-0">expand_more</span>
                        </summary>
                        <div class="px-6 pb-6 pt-2 font-body-md text-body-md text-on-surface-variant whitespace-pre-line">{{ $faq->answer }}</div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
