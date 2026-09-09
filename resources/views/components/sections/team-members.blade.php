@props(['members'])

@if ($members->isNotEmpty())
    <section class="py-24 px-6 max-w-7xl mx-auto">
        <div class="mb-16 text-center">
            <span class="text-sm font-bold text-secondary uppercase tracking-widest block mb-4">Tim Kami</span>
            <h2 class="font-headline-lg text-headline-lg md:text-5xl text-primary">Orang di balik {{ app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name') }}</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @foreach ($members as $member)
                @php
                    $hasPhoto = $member->photo_path
                        && \Illuminate\Support\Facades\Storage::disk('public')->exists($member->photo_path);
                @endphp
                <div class="text-center">
                    @if ($hasPhoto)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($member->photo_path) }}"
                             alt="{{ $member->name }}"
                             class="w-full aspect-square object-cover rounded-lg mb-4 bg-surface-container">
                    @else
                        <div class="w-full aspect-square rounded-lg mb-4 bg-surface-container-high text-primary flex items-center justify-center font-headline-lg text-4xl">
                            {{ \Illuminate\Support\Str::of($member->name)->substr(0, 1)->upper() }}
                        </div>
                    @endif

                    <h3 class="font-headline-lg text-headline-lg text-lg text-primary">{{ $member->name }}</h3>
                    <p class="text-sm font-label-bold text-label-bold text-secondary mb-2">{{ $member->position }}</p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">{{ $member->bio }}</p>

                    @if ($member->linkedin_url)
                        <a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener noreferrer nofollow"
                           class="inline-flex items-center gap-1 text-primary text-sm font-label-bold text-label-bold mt-3 hover:underline">
                            <span class="material-symbols-outlined text-base">link</span> LinkedIn
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif
