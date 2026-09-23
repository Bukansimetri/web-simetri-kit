@extends('layouts.public')

@php
    $appName = app(\App\Settings\SiteSettings::class)->site_name ?: config('app.name');
    $images = $project->imageUrls();
@endphp

@section('title', \App\Support\Seo\PageTitle::forContent('portfolio_show', $project->meta_title, $project->title))
@section('meta_description', $project->seoDescription())
@section('og_title', $project->seoTitle())
@section('og_image', $project->seoImageUrl() ?? app(\App\Settings\SocialSettings::class)->ogImageUrl())

@section('content')
    <article class="pt-32 pb-24 px-6 max-w-4xl mx-auto">
        <p class="text-sm font-semibold text-primary/70 uppercase tracking-widest mb-4">
            <a href="{{ url('/portfolio') }}" class="hover:text-primary transition-colors">Portfolio</a>
            <span class="mx-2">/</span>
            <a href="{{ url('/portfolio').'?kategori='.$project->portfolioCategory->slug }}" class="hover:text-primary transition-colors">{{ $project->portfolioCategory->name }}</a>
        </p>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-8">{{ $project->title }}</h1>

        @if (! empty($images))
            <div class="space-y-4 mb-10">
                <div class="aspect-video w-full bg-surface-container rounded-lg overflow-hidden">
                    <img src="{{ $images[0] }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
                </div>
                @if (count($images) > 1)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach (array_slice($images, 1) as $url)
                            <div class="aspect-video bg-surface-container rounded-lg overflow-hidden">
                                <img src="{{ $url }}" alt="{{ $project->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if ($project->client_name || $project->completed_at || $project->project_url)
            <dl class="flex flex-wrap gap-x-10 gap-y-3 mb-10 pb-10 border-b border-surface-container-low text-sm">
                @if ($project->client_name)
                    <div>
                        <dt class="text-outline uppercase tracking-widest text-xs mb-1">Klien</dt>
                        <dd class="text-on-surface font-label-bold text-label-bold">{{ $project->client_name }}</dd>
                    </div>
                @endif
                @if ($project->completed_at)
                    <div>
                        <dt class="text-outline uppercase tracking-widest text-xs mb-1">Selesai</dt>
                        <dd class="text-on-surface font-label-bold text-label-bold">{{ $project->completed_at->translatedFormat('F Y') }}</dd>
                    </div>
                @endif
                @if ($project->project_url)
                    <div>
                        <dt class="text-outline uppercase tracking-widest text-xs mb-1">Tautan</dt>
                        <dd>
                            <a href="{{ $project->project_url }}" target="_blank" rel="noopener noreferrer nofollow" class="text-primary font-label-bold text-label-bold hover:underline inline-flex items-center gap-1">
                                Kunjungi proyek <span class="material-symbols-outlined text-sm">open_in_new</span>
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>
        @endif

        <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed space-y-4">
            {!! $project->description !!}
        </div>
    </article>
@endsection
