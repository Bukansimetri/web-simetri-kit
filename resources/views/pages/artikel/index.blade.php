@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', 'Artikel & Blog — '.$appName)
@section('meta_description', 'Tips, edukasi, dan berita seputar energi surya dari '.$appName.'.')

@section('content')
    <section class="pt-32 pb-16 px-6 max-w-7xl mx-auto text-center">
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">Artikel &amp; Blog</h1>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto">
            Tips, edukasi, dan kabar terbaru seputar energi surya.
        </p>
    </section>

    <section class="px-6 max-w-7xl mx-auto pb-24">
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
