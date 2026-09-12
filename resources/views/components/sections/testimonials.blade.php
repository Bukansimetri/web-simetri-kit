@props(['testimonials'])

@if ($testimonials->isNotEmpty())
    <section class="py-24 px-6 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="mb-16 text-center">
                <span class="text-sm font-bold text-secondary uppercase tracking-widest block mb-4">Testimoni</span>
                <h2 class="font-headline-lg text-headline-lg md:text-5xl text-primary">Apa Kata Klien Kami</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($testimonials as $testimonial)
                    @php
                        $hasPhoto = $testimonial->photo_path
                            && \Illuminate\Support\Facades\Storage::disk('public')->exists($testimonial->photo_path);
                    @endphp
                    <figure class="bg-white p-8 shadow-md border border-surface-container-low rounded-lg flex flex-col">
                        <div class="flex items-center gap-1 mb-4 text-secondary" aria-label="Rating {{ $testimonial->rating }} dari 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="material-symbols-outlined text-xl">{{ $i <= $testimonial->rating ? 'star' : 'star_border' }}</span>
                            @endfor
                        </div>

                        <blockquote class="font-body-md text-body-md text-on-surface-variant leading-relaxed flex-1">
                            {{ $testimonial->content }}
                        </blockquote>

                        <figcaption class="flex items-center gap-4 mt-6 pt-6 border-t border-surface-container-low">
                            @if ($hasPhoto)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($testimonial->photo_path) }}"
                                     alt="{{ $testimonial->name }}"
                                     loading="lazy" decoding="async"
                                     class="w-12 h-12 rounded-full object-cover shrink-0">
                            @else
                                <span class="w-12 h-12 rounded-full bg-surface-container-high text-primary flex items-center justify-center font-headline-lg text-lg shrink-0">
                                    {{ \Illuminate\Support\Str::of($testimonial->name)->substr(0, 1)->upper() }}
                                </span>
                            @endif
                            <div>
                                <p class="font-headline-lg text-headline-lg text-base text-primary">{{ $testimonial->name }}</p>
                                @if ($testimonial->attribution)
                                    <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $testimonial->attribution }}</p>
                                @endif
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
