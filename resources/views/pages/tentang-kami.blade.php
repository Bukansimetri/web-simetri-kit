@extends('layouts.public')

@use('App\Enums\PublicSection')
@use('App\Support\PageContent\SectionVisibility')

@php
    $appName = app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name');
    $hero = \App\Support\PageContent\PageContent::block(\App\Enums\PageBlockType::AboutHero);
    $whoWeAre = \App\Support\PageContent\PageContent::block(\App\Enums\PageBlockType::AboutWhoWeAre);
    $vision = \App\Support\PageContent\PageContent::block(\App\Enums\PageBlockType::AboutVision);
    $mission = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::AboutMission);
    $values = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::AboutValues);
    $trust = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::AboutTrust);
@endphp

@section('title', \App\Support\Seo\PageTitle::forStatic('tentang_kami', 'Tentang Kami'))
@section('meta_description', 'Mengenal '.$appName.' lebih dekat — visi, misi, dan nilai-nilai kami dalam menghadirkan solusi energi surya.')

@section('content')
    <x-sections.page-hero
        :title="$hero->value('title')"
        breadcrumb="Tentang Kami"
        :subtitle="$hero->value('subtitle')"
        :image="\App\Support\PageContent\PageContent::imageUrl($hero->value('image_path'), \App\Enums\PageBlockType::AboutHero->defaultImagePath())"
    />

    @if (SectionVisibility::shows(PublicSection::AboutWhoWeAre))
    {{-- Siapa Kami --}}
    <section class="reveal-element py-24 px-6 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row gap-16 md:gap-24 items-center">
            <div class="w-full md:w-[40%] relative">
                <div class="overflow-hidden shadow-lg aspect-square rounded-lg">
                    <img src="{{ \App\Support\PageContent\PageContent::imageUrl($whoWeAre->value('image_path'), 'images/mockup/tentang-kami-2.jpg') }}" alt="Panel surya berkualitas tinggi memantulkan langit" loading="lazy" decoding="async" class="w-full h-full object-cover">
                </div>
                <div class="absolute -bottom-6 -right-6 md:-right-12 bg-white p-6 shadow-lg -rotate-3 max-w-[220px] border border-gray-100 rounded-lg">
                    <p class="font-semibold text-sm text-primary text-center">{{ $whoWeAre->value('badge_text') }}</p>
                </div>
            </div>
            <div class="w-full md:w-[60%]">
                <span class="text-sm font-bold text-secondary uppercase tracking-widest block mb-4">{{ $whoWeAre->value('eyebrow') }}</span>
                <h2 class="font-headline-xl text-3xl md:text-4xl font-bold tracking-tight leading-tight text-primary mb-6">{{ $whoWeAre->value('heading') }}</h2>
                <div class="text-lg text-on-surface-variant mb-8 leading-relaxed [&_p]:mb-0">
                    {!! \App\Support\PageContent\PageContent::richText($whoWeAre->value('body')) !!}
                </div>
                @if (filled(trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags((string) $whoWeAre->value('quote')), ENT_QUOTES | ENT_HTML5)))))
                    <div class="pl-8 border-l-4 border-secondary">
                        <div class="font-headline-lg text-2xl md:text-3xl font-bold text-primary leading-snug [&_p]:mb-0">
                            &ldquo;{!! \App\Support\PageContent\PageContent::richText($whoWeAre->value('quote')) !!}&rdquo;
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    @if (SectionVisibility::shows(PublicSection::AboutVision))
    {{-- Visi --}}
    <section class="reveal-element py-32 px-6 bg-white relative overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[300px] md:text-[400px] text-surface-container-high/50 font-serif leading-none select-none z-0">&rdquo;</div>
        <div class="relative z-10 max-w-4xl mx-auto text-center">
            <p class="text-sm font-bold text-outline uppercase tracking-[0.3em] mb-4">{{ $vision->value('eyebrow') }}</p>
            <h2 class="font-headline-xl text-3xl md:text-4xl font-bold tracking-tight leading-tight text-primary mb-4">
                {{ $vision->value('heading') }}
            </h2>
            <p class="text-secondary font-medium text-base md:text-lg max-w-2xl mx-auto leading-relaxed">{{ $vision->value('subtext') }}</p>
        </div>
    </section>
    @endif

    @if (SectionVisibility::shows(PublicSection::AboutMission))
    {{-- Misi --}}
    @if ($mission)
    <section class="reveal-element py-24 px-6 max-w-7xl mx-auto">
        <div class="text-center max-w-3xl mx-auto mb-16">
            @if (filled($mission->eyebrow))
                <span class="text-sm font-bold text-secondary uppercase tracking-widest block mb-4">{{ $mission->eyebrow }}</span>
            @endif
            <h2 class="font-headline-xl text-3xl md:text-4xl font-bold tracking-tight leading-tight mb-3 text-primary">{{ \App\Support\PageContent\PageContent::multiline($mission->title) }}</h2>
            @if (filled($mission->subtitle))
                <p class="text-secondary text-base md:text-lg font-medium max-w-2xl mx-auto leading-relaxed">{{ $mission->subtitle }}</p>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-12 md:gap-24">
            <div class="md:col-span-3 space-y-10">
                @foreach ($mission->items->take(3) as $point)
                    <div class="flex gap-5">
                        <span class="material-symbols-outlined text-secondary shrink-0 text-3xl mt-0.5">check_circle</span>
                        <div>
                            <h3 class="font-headline-lg text-lg md:text-xl font-bold leading-snug text-primary mb-2">{{ $point->title }}</h3>
                            <p class="text-on-surface-variant leading-relaxed">{{ $point->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="md:col-span-2 space-y-10">
                @foreach ($mission->items->slice(3) as $point)
                    <div class="flex gap-5">
                        <span class="material-symbols-outlined text-secondary shrink-0 text-3xl mt-0.5">check_circle</span>
                        <div>
                            <h3 class="font-headline-lg text-lg md:text-xl font-bold leading-snug text-primary mb-2">{{ $point->title }}</h3>
                            <p class="text-on-surface-variant leading-relaxed">{{ $point->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endif

    @if (SectionVisibility::shows(PublicSection::AboutValues))
    {{-- Nilai (Bento Grid) --}}
    @if ($values)
    <section class="reveal-element py-24 px-6 max-w-7xl mx-auto mb-12">
        <div class="mb-12 text-center">
            <h2 class="font-headline-xl text-3xl md:text-4xl font-bold tracking-tight leading-tight mb-3 text-primary">{{ \App\Support\PageContent\PageContent::multiline($values->title) }}</h2>
            @if (filled($values->subtitle))
                <p class="text-secondary text-base md:text-lg font-medium max-w-2xl mx-auto leading-relaxed">{{ $values->subtitle }}</p>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-8 items-stretch">
            <div class="md:col-span-3 relative rounded-lg overflow-hidden shadow-md min-h-[400px] flex flex-col justify-end p-8 md:p-12 group">
                <img src="{{ \App\Support\PageContent\PageContent::imageUrl($values->featured['image_path'], 'images/mockup/tentang-kami-3.jpg') }}" alt="{{ $values->featured['title'] }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-inverse-surface/90 via-inverse-surface/50 to-transparent"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-primary-container text-white flex items-center justify-center mb-4 rounded-lg shadow-md">
                        <span class="material-symbols-outlined text-2xl">{{ $values->featured['icon'] }}</span>
                    </div>
                    <h3 class="font-headline-lg text-lg md:text-xl font-bold leading-snug text-white mb-3">{{ $values->featured['title'] }}</h3>
                    <p class="text-white/90 text-sm md:text-base max-w-xl leading-relaxed">{{ $values->featured['description'] }}</p>
                </div>
            </div>
            <div class="md:col-span-2 flex flex-col gap-6 justify-between">
                @foreach ($values->items as $value)
                    <div class="bg-white p-6 md:p-8 rounded-lg shadow-md border border-surface-container-low transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-surface-container-high flex items-center justify-center text-primary shrink-0 rounded-lg">
                                <span class="material-symbols-outlined text-2xl">{{ $value->icon }}</span>
                            </div>
                            <div>
                                <h3 class="font-headline-lg text-lg md:text-xl font-bold leading-snug text-primary mb-1.5">{{ $value->title }}</h3>
                                <p class="text-on-surface-variant text-sm leading-relaxed">{{ $value->description }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endif

    @if (SectionVisibility::shows(PublicSection::AboutTrust))
    {{-- Trust Strip --}}
    @if ($trust)
    <section class="reveal-element py-16 bg-white border-y border-surface-container-low">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 md:gap-0 divide-y md:divide-y-0 md:divide-x divide-outline-variant">
                @foreach ($trust->items as $stat)
                    <div class="w-full md:flex-1 text-center py-4 md:py-0">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-primary text-4xl mb-1">{{ $stat->icon }}</span>
                            <span class="font-headline-lg text-4xl font-bold text-primary tracking-tight">{{ $stat->title }}</span>
                            <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">{{ $stat->description }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endif

    @if (SectionVisibility::shows(PublicSection::AboutTeam))
        <x-sections.team-members :members="$teamMembers" />
    @endif

    @if (SectionVisibility::shows(PublicSection::AboutTestimonials))
        <x-sections.testimonials :testimonials="$testimonials" />
    @endif

    @if (SectionVisibility::shows(PublicSection::AboutClientLogos))
        <x-sections.client-logos :logos="$clientLogos" />
    @endif

    <x-sections.cta-band :placement="\App\Enums\CtaPlacement::About" />
@endsection
