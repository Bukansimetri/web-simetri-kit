@props(['banners'])

@php
    $visible = $banners
        ->filter(fn ($b) => $b->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($b->image_path))
        ->values();
@endphp

@if ($visible->isNotEmpty())
    @if ($visible->count() === 1)
        @php($banner = $visible->first())
        <section class="w-full">
            @if ($banner->link_url)
                <a href="{{ $banner->link_url }}" class="block">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path) }}"
                         alt="{{ $banner->alt_text }}" class="w-full h-[45vh] md:h-[60vh] object-cover">
                </a>
            @else
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path) }}"
                     alt="{{ $banner->alt_text }}" class="w-full h-[45vh] md:h-[60vh] object-cover">
            @endif
        </section>
    @else
        <section
            x-data="{
                active: 0,
                count: {{ $visible->count() }},
                timer: null,
                start() { this.timer = setInterval(() => this.next(), 5000); },
                stop() { clearInterval(this.timer); },
                next() { this.active = (this.active + 1) % this.count; },
                prev() { this.active = (this.active + this.count - 1) % this.count; },
                go(i) { this.active = i; },
            }"
            x-init="start()"
            @mouseenter="stop()" @mouseleave="start()"
            class="relative w-full overflow-hidden"
        >
            @foreach ($visible as $i => $banner)
                <div x-show="active === {{ $i }}" x-transition.opacity.duration.500ms class="w-full">
                    @if ($banner->link_url)
                        <a href="{{ $banner->link_url }}" class="block">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path) }}"
                                 alt="{{ $banner->alt_text }}" class="w-full h-[45vh] md:h-[60vh] object-cover">
                        </a>
                    @else
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path) }}"
                             alt="{{ $banner->alt_text }}" class="w-full h-[45vh] md:h-[60vh] object-cover">
                    @endif
                </div>
            @endforeach

            <button type="button" @click="prev()" aria-label="Banner sebelumnya"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/80 hover:bg-white flex items-center justify-center shadow-md">
                <span class="material-symbols-outlined text-on-surface">chevron_left</span>
            </button>
            <button type="button" @click="next()" aria-label="Banner berikutnya"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/80 hover:bg-white flex items-center justify-center shadow-md">
                <span class="material-symbols-outlined text-on-surface">chevron_right</span>
            </button>

            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                @foreach ($visible as $i => $banner)
                    <button type="button" @click="go({{ $i }})" aria-label="Banner {{ $i + 1 }}"
                            class="w-2.5 h-2.5 rounded-full transition-colors"
                            :class="active === {{ $i }} ? 'bg-white' : 'bg-white/50'"></button>
                @endforeach
            </div>
        </section>
    @endif
@endif
