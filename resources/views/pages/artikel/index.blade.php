@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', 'Artikel & Blog — '.$appName)
@section('meta_description', 'Tips, edukasi, dan berita seputar energi surya dari '.$appName.'.')

@section('content')
    <x-sections.page-hero
        title="Artikel & Blog"
        breadcrumb="Artikel"
        subtitle="Tips, edukasi, dan kabar terbaru seputar energi surya."
        :image="asset('images/mockup/artikel-1.jpg')"
    />

    <section class="reveal-element px-6 max-w-7xl mx-auto py-24">
        @if ($articles->isEmpty())
            <p class="text-center text-on-surface-variant py-16">Belum ada artikel yang dipublikasikan saat ini.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
                @foreach ($articles as $article)
                    <x-sections.article-card :article="$article" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
