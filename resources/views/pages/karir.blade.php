@extends('layouts.public')

@use('App\Enums\PublicSection')
@use('App\Support\PageContent\SectionVisibility')

@php
    $appName = app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name');

    $values = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::CareerValues);
    $process = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::RecruitmentProcess);
    $hero = \App\Support\PageContent\PageContent::block(\App\Enums\PageBlockType::CareerHero);
@endphp

@section('title', \App\Support\Seo\PageTitle::forStatic('karir', 'Karir'))
@section('meta_description', 'Bergabunglah dengan tim '.$appName.' dan jadi bagian dari transisi energi bersih Indonesia.')

@section('content')
    <x-sections.page-hero
        :title="$hero->value('title')"
        breadcrumb="Karir"
        :subtitle="$hero->value('subtitle')"
        :image="\App\Support\PageContent\PageContent::imageUrl($hero->value('image_path'), \App\Enums\PageBlockType::CareerHero->defaultImagePath())"
    />

    @if (SectionVisibility::shows(PublicSection::CareerValues))
    {{-- Values --}}
    @if ($values)
    <section class="reveal-element px-6 max-w-7xl mx-auto py-20">
        <div class="text-center mb-12">
            <h2 class="font-headline-lg text-2xl md:text-3xl font-bold mb-2 text-primary-container">{{ \App\Support\PageContent\PageContent::multiline($values->title) }}</h2>
            @if (filled($values->subtitle))
                <p class="font-body-md text-body-md text-on-surface-variant">{{ $values->subtitle }}</p>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ($values->items as $value)
                <div class="bg-white rounded-xl p-8 border border-surface-container-low hover:border-primary-fixed-dim transition-colors group">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-white">{{ $value->icon }}</span>
                    </div>
                    <h3 class="font-headline-lg text-xl mb-3 text-on-surface">{{ $value->title }}</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">{{ $value->description }}</p>
                </div>
            @endforeach
        </div>
    </section>
    @endif
    @endif

    {{-- Open Positions --}}
    <section id="positions" class="reveal-element px-6 max-w-5xl mx-auto py-12">
        <div class="text-center mb-12">
            <h2 class="font-headline-lg text-2xl md:text-3xl font-bold mb-2 text-primary-container">Posisi Terbuka</h2>
        </div>

        @if ($jobOpenings->isEmpty())
            <div class="bg-surface-container-low rounded-lg p-8 text-center">
                <p class="text-on-surface-variant">Belum ada lowongan terbuka saat ini. Kirim CV Anda untuk masuk daftar tunggu kami.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($jobOpenings as $job)
                    <x-sections.job-card :job="$job" />
                @endforeach
            </div>
        @endif
    </section>

    @if (SectionVisibility::shows(PublicSection::CareerProcess))
    {{-- Recruitment Process --}}
    @if ($process)
    <section class="reveal-element px-6 max-w-7xl mx-auto py-12 mb-12">
        <div class="bg-surface-container-low rounded-2xl p-8 md:p-12 border border-surface-container">
            <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-center mb-10 text-primary-container">{{ \App\Support\PageContent\PageContent::multiline($process->title) }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                <div class="hidden md:block absolute top-6 left-[12.5%] right-[12.5%] h-0.5 bg-outline-variant z-0"></div>
                @foreach ($process->items as $step)
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-12 h-12 rounded-full bg-primary-container text-white flex items-center justify-center font-label-bold text-label-bold text-lg mb-4 shadow-sm">{{ \App\Enums\PageSection::RecruitmentProcess->stepNumber($loop->iteration) }}</div>
                        <h4 class="font-headline-lg text-base font-bold mb-2 text-on-surface">{{ $step->title }}</h4>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $step->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endif

    <x-sections.cta-band
        :placement="\App\Enums\CtaPlacement::Career"
        :buttonHref="url('/kontak')"
    />
@endsection
