@extends('layouts.public')

@php
    $appName = app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name');
    $hero = \App\Support\PageContent\PageContent::block(\App\Enums\PageBlockType::CareerHero);
    $employmentType = \App\Models\JobOpening::EMPLOYMENT_TYPES[$job->employment_type] ?? \Illuminate\Support\Str::of($job->employment_type)->replace('-', ' ')->title();
@endphp

@section('title', \App\Support\Seo\PageTitle::forContent('karir_show', null, $job->title))
@section('meta_description', \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $job->description))), 155))

@section('content')
    <x-sections.page-hero
        :title="$job->title"
        breadcrumb="Karir"
        :subtitle="$job->location.' · '.$employmentType"
        :image="\App\Support\PageContent\PageContent::imageUrl($hero->value('image_path'), \App\Enums\PageBlockType::CareerHero->defaultImagePath())"
    />

    <article class="reveal-element px-6 max-w-3xl mx-auto py-16">
        <div class="flex flex-wrap items-center gap-3 mb-8">
            <span class="bg-surface-container-high text-on-surface text-xs px-3 py-1 rounded-md font-label-bold text-label-bold">{{ $employmentType }}</span>
            <span class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">location_on</span> {{ $job->location }}
            </span>
        </div>

        <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed whitespace-pre-line">{{ $job->description }}</div>

        <div class="mt-12 flex flex-col sm:flex-row gap-4">
            <a href="{{ url('/kontak') }}" class="btn-fill inline-flex items-center justify-center gap-2 bg-primary-container text-white px-8 py-4 rounded-lg font-label-bold text-label-bold font-bold hover:scale-105 transition-transform">
                Lamar Sekarang <span class="material-symbols-outlined">arrow_forward</span>
            </a>
            <a href="{{ route('karir') }}" class="inline-flex items-center justify-center gap-2 border border-outline-variant text-on-surface px-8 py-4 rounded-lg font-label-bold text-label-bold font-bold hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined">arrow_back</span> Kembali ke Karir
            </a>
        </div>
    </article>
@endsection
