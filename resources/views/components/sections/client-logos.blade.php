@props(['logos'])
@php($heading = \App\Support\PageContent\PageContent::heading('tentang-kami.logo-klien'))

@php
    $visibleLogos = $logos->filter(fn ($logo) => $logo->logo_path
        && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo->logo_path));
@endphp

@if ($visibleLogos->isNotEmpty())
    <section class="py-20 px-6 bg-surface-container-low">
        <div class="max-w-7xl mx-auto text-center">
            <div class="mb-12">
                <h2 class="text-sm font-bold text-outline uppercase tracking-[0.3em]">{{ $heading->title }}</h2>
                @if (filled($heading->subtitle))<p class="mt-3 text-secondary">{{ $heading->subtitle }}</p>@endif
            </div>

            <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-8">
                @foreach ($visibleLogos as $logo)
                    @if ($logo->link_url)
                        <a href="{{ $logo->link_url }}" target="_blank" rel="noopener noreferrer nofollow" title="{{ $logo->company_name }}">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo->logo_path) }}" alt="{{ $logo->company_name }}" loading="lazy" decoding="async" class="h-10 md:h-12 w-auto object-contain opacity-70 hover:opacity-100 transition-opacity">
                        </a>
                    @else
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo->logo_path) }}" alt="{{ $logo->company_name }}" loading="lazy" decoding="async" class="h-10 md:h-12 w-auto object-contain opacity-70 hover:opacity-100 transition-opacity">
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
