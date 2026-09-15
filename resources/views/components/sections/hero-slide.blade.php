@props(['banner', 'index' => 0, 'total' => 1])

@php
    $isFirst = $index === 0;
    $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path);
    $overlay = $banner->overlay_style;
    $position = $banner->text_position;
    $wrapImageInLink = ! $banner->hasCta() && filled($banner->link_url);
    $overlayClasses = $overlay->overlayClasses($position);
    $headingClasses = $overlay->headingClasses();
    $bodyClasses = $overlay->bodyClasses();
@endphp

<div class="relative w-full h-full min-h-[640px] flex items-center pt-32 pb-24 px-6 overflow-hidden">
    <div class="absolute inset-0 z-0">
        @if ($wrapImageInLink)
            <a href="{{ $banner->link_url }}" class="block w-full h-full" aria-label="{{ $banner->alt_text }}">
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $banner->alt_text }}"
                    @if ($isFirst) fetchpriority="high" @else loading="lazy" decoding="async" @endif
                    class="w-full h-full object-cover"
                >
            </a>
        @else
            <img
                src="{{ $imageUrl }}"
                alt="{{ $banner->alt_text }}"
                @if ($isFirst) fetchpriority="high" @else loading="lazy" decoding="async" @endif
                class="w-full h-full object-cover"
            >
        @endif

        @if ($overlayClasses !== '')
            <div class="{{ $overlayClasses }}"></div>
        @endif
    </div>

    @if ($banner->hasContent())
        <div class="max-w-7xl mx-auto w-full relative z-10 py-12">
            <div class="flex flex-col {{ $position->containerClasses() }}">
                @if (filled($banner->badge_text))
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 text-xs font-semibold mb-6 backdrop-blur-md {{ $headingClasses }}">
                        <span class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></span>
                        <span>{{ $banner->badge_text }}</span>
                    </div>
                @endif

                @if (filled($banner->heading))
                    @if ($isFirst)
                        <h1 class="font-headline-xl text-3xl md:text-5xl font-extrabold tracking-tight leading-tight mb-6 {{ $headingClasses }}">
                            {{ $banner->heading }}
                        </h1>
                    @else
                        <h2 class="font-headline-xl text-3xl md:text-5xl font-extrabold tracking-tight leading-tight mb-6 {{ $headingClasses }}">
                            {{ $banner->heading }}
                        </h2>
                    @endif
                @endif

                @if (filled($banner->subheading))
                    <p class="text-base md:text-lg font-medium leading-relaxed mb-8 max-w-xl {{ $bodyClasses }}">
                        {{ $banner->subheading }}
                    </p>
                @endif

                @if ($banner->hasCta())
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mb-10">
                        @if ($banner->hasPrimaryCta())
                            <a href="{{ url($banner->cta_primary_url) }}" class="btn-fill bg-primary-container text-white font-bold px-8 py-4 rounded-lg shadow-lg hover:shadow-xl hover:scale-105 transition-all flex items-center justify-center gap-2 group">
                                <span>{{ $banner->cta_primary_label }}</span>
                                <span class="material-symbols-outlined text-lg group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </a>
                        @endif

                        @if ($banner->hasSecondaryCta())
                            <a href="{{ url($banner->cta_secondary_url) }}" class="bg-white/10 hover:bg-white/20 border border-white/30 font-semibold px-8 py-4 rounded-lg transition-colors flex items-center justify-center {{ $headingClasses }}">
                                {{ $banner->cta_secondary_label }}
                            </a>
                        @endif
                    </div>
                @endif

                @if ($banner->sanitizedTrustHtml())
                    <div class="pt-4 border-t border-current/15 text-xs {{ $bodyClasses }}">
                        {!! $banner->sanitizedTrustHtml() !!}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
