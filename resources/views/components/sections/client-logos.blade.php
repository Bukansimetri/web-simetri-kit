@props(['logos'])

@php
    $visibleLogos = $logos->filter(fn ($logo) => $logo->logo_path
        && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo->logo_path));
@endphp

@if ($visibleLogos->isNotEmpty())
    <section class="py-20 px-6 bg-surface-container-low">
        <div class="max-w-7xl mx-auto text-center">
            <p class="text-sm font-bold text-outline uppercase tracking-[0.3em] mb-12">Dipercaya oleh</p>

            <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-8">
                @foreach ($visibleLogos as $logo)
                    @php
                        $img = '<img src="'.e(\Illuminate\Support\Facades\Storage::disk('public')->url($logo->logo_path)).'" alt="'.e($logo->company_name).'" class="h-10 md:h-12 w-auto object-contain opacity-70 hover:opacity-100 transition-opacity">';
                    @endphp
                    @if ($logo->link_url)
                        <a href="{{ $logo->link_url }}" target="_blank" rel="noopener noreferrer nofollow" title="{{ $logo->company_name }}">
                            {!! $img !!}
                        </a>
                    @else
                        {!! $img !!}
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
