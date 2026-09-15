@props(['banners'])

@php
    $visible = $banners
        ->filter(fn ($b) => $b->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($b->image_path))
        ->values();
    $count = $visible->count();
@endphp

@if ($count === 1)
    <section class="relative w-full overflow-hidden">
        <x-sections.hero-slide :banner="$visible->first()" :index="0" :total="1" />
    </section>
@elseif ($count > 1)
    <section
        x-data="heroSlider({{ $count }})"
        x-init="init($el)"
        class="relative w-full min-h-[640px] overflow-hidden"
        role="group"
        aria-roledescription="carousel"
        aria-label="Slide promosi beranda"
    >
        @foreach ($visible as $i => $banner)
            <div
                class="absolute inset-0 transition-opacity duration-500 motion-reduce:transition-none {{ $i === 0 ? 'opacity-100' : 'opacity-0 pointer-events-none' }}"
                x-bind:class="{ 'opacity-100': active === {{ $i }}, 'opacity-0 pointer-events-none': active !== {{ $i }} }"
                x-bind:aria-hidden="active === {{ $i }} ? 'false' : 'true'"
                x-bind:inert="active !== {{ $i }}"
                @if ($i !== 0) aria-hidden="true" inert @endif
                role="group"
                aria-roledescription="slide"
                aria-label="Slide {{ $i + 1 }} dari {{ $count }}"
            >
                <x-sections.hero-slide :banner="$banner" :index="$i" :total="$count" />
            </div>
        @endforeach

        <button
            type="button"
            x-on:click="prev()"
            aria-label="Slide sebelumnya"
            class="hero-slider-arrow hero-slider-arrow--prev absolute left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/80 hover:bg-white flex items-center justify-center shadow-md max-md:hidden"
        >
            <span class="material-symbols-outlined text-on-surface">chevron_left</span>
        </button>
        <button
            type="button"
            x-on:click="next()"
            aria-label="Slide berikutnya"
            class="hero-slider-arrow hero-slider-arrow--next absolute right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/80 hover:bg-white flex items-center justify-center shadow-md max-md:hidden"
        >
            <span class="material-symbols-outlined text-on-surface">chevron_right</span>
        </button>

        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-4">
            <div class="flex gap-2" role="tablist" aria-label="Pilih slide">
                @foreach ($visible as $i => $banner)
                    <button
                        type="button"
                        role="tab"
                        x-on:click="go({{ $i }})"
                        x-bind:aria-selected="active === {{ $i }} ? 'true' : 'false'"
                        aria-label="Slide {{ $i + 1 }}"
                        class="w-2.5 h-2.5 rounded-full transition-colors"
                        x-bind:class="{ 'bg-white': active === {{ $i }}, 'bg-white/50': active !== {{ $i }} }"
                    ></button>
                @endforeach
            </div>
            <span class="text-white text-xs font-semibold bg-black/30 px-2 py-1 rounded-full" x-text="`${active + 1} / {{ $count }}`">1 / {{ $count }}</span>
        </div>

        <div
            class="hero-slider-swipe-hint absolute bottom-16 left-1/2 -translate-x-1/2 z-20 hidden max-md:flex items-center gap-2 text-white text-xs font-semibold bg-black/30 px-3 py-1.5 rounded-full"
            x-show="!hasInteracted"
            x-on:touchstart.once="hasInteracted = true"
        >
            <span class="material-symbols-outlined text-sm hero-slider-swipe-icon">swipe</span>
            <span>Geser untuk melihat lainnya</span>
        </div>
    </section>
@endif
