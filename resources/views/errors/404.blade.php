@extends('layouts.public')

@php
    $site404 = app(\App\Settings\SiteSettings::class);
@endphp

@section('title', \App\Support\Seo\PageTitle::forStatic('home', 'Halaman Tidak Ditemukan'))

@section('content')
    <section class="pt-40 pb-24 px-6 text-center max-w-2xl mx-auto">
        <p class="text-sm font-bold text-secondary uppercase tracking-widest mb-4">404</p>
        <h1 class="font-headline-lg text-headline-lg text-3xl md:text-4xl text-primary mb-4">
            {{ $site404->error_404_message ?: 'Halaman yang Anda cari tidak ditemukan.' }}
        </h1>
        <p class="text-on-surface-variant mb-8">
            Alamat yang Anda tuju mungkin sudah dipindahkan atau tidak pernah ada.
        </p>
        <a href="{{ url('/') }}" class="btn-fill inline-flex items-center justify-center gap-2 bg-primary-container text-white font-bold px-8 py-4 rounded-lg hover:scale-105 transition-transform">
            Kembali ke Beranda
        </a>
    </section>
@endsection
