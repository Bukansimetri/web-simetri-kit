@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', $customPage->title.' — '.$appName)
@section('meta_description', $customPage->seoDescription())
@section('og_title', $customPage->seoTitle())
@section('og_image', $customPage->seoImageUrl() ?? app(\App\Settings\BrandSettings::class)->ogImageUrl())

@section('content')
    <article class="pt-32 pb-24 px-6 max-w-3xl mx-auto">
        <p class="text-sm font-semibold text-primary/70 uppercase tracking-widest mb-4">
            <a href="{{ url('/') }}" class="hover:text-primary transition-colors">Beranda</a>
            <span class="mx-2">/</span>
            {{ $customPage->title }}
        </p>
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-8">{{ $customPage->title }}</h1>

        <div class="font-body-md text-body-md text-on-surface-variant leading-relaxed space-y-4">
            {!! $customPage->content !!}
        </div>
    </article>
@endsection
