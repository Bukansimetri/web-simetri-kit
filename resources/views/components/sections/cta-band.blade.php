@props([
    'placement',
    'buttonIcon' => 'forum',
    'buttonHref' => null,
])

@php
    $site = app(\App\Settings\SiteSettings::class);
    $href = $buttonHref ?: ($site->whatsappUrl(\App\Support\PageContent\PageContent::whatsappMessage()) ?: url('/kontak'));
    $cta = \App\Support\PageContent\PageContent::cta($placement);
@endphp

@if (\App\Support\PageContent\SectionVisibility::shows(\App\Enums\PublicSection::fromCta($placement)))
<section class="reveal-element py-24 px-6 bg-primary text-center">
    <div class="max-w-4xl mx-auto">
        <h2 class="font-headline-xl text-3xl md:text-5xl font-bold text-white mb-10 leading-tight">
            {{ \App\Support\PageContent\PageContent::multiline($cta->title) }}@if (filled($cta->body))<br>{{ $cta->body }}@endif
        </h2>
        <a href="{{ $href }}" class="btn-fill inline-flex items-center justify-center gap-3 text-white font-bold text-lg px-10 py-5 hover:scale-105 transition-transform shadow-lg bg-primary-container hover:bg-primary-container/90 rounded-lg">
            <span class="material-symbols-outlined">{{ $buttonIcon }}</span>
            {{ $cta->primary_label }}
        </a>
    </div>
</section>
@endif
