@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', 'Karir — '.$appName)
@section('meta_description', 'Bergabunglah dengan tim '.$appName.' dan jadi bagian dari transisi energi bersih Indonesia.')

@section('content')
    <section class="pt-32 pb-16 px-6 max-w-7xl mx-auto text-center">
        <h1 class="font-headline-xl text-headline-xl text-on-surface mb-4">Bangun Karir di {{ $appName }}</h1>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto">
            Jadilah bagian dari tim yang menggerakkan transisi energi bersih di Indonesia.
        </p>
    </section>

    @php
        $values = [
            ['icon' => 'diversity_3', 'title' => 'Kolaboratif', 'description' => 'Bekerja lintas tim untuk memberikan solusi terbaik bagi klien.'],
            ['icon' => 'trending_up', 'title' => 'Bertumbuh', 'description' => 'Kesempatan belajar dan berkembang di industri energi terbarukan.'],
            ['icon' => 'eco', 'title' => 'Berdampak', 'description' => 'Setiap peran berkontribusi langsung pada masa depan energi bersih.'],
        ];
    @endphp
    <section class="px-6 max-w-7xl mx-auto py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ($values as $value)
                <div class="bg-white p-8 rounded-lg shadow-md border border-outline-variant/20 text-center">
                    <span class="material-symbols-outlined text-primary text-3xl mb-4">{{ $value['icon'] }}</span>
                    <h3 class="font-bold text-primary mb-2">{{ $value['title'] }}</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $value['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="px-6 max-w-5xl mx-auto py-16">
        <h2 class="font-headline-lg text-headline-lg text-primary mb-10 text-center">Lowongan Terbuka</h2>

        @if ($jobOpenings->isEmpty())
            <p class="text-center text-on-surface-variant">Belum ada lowongan terbuka saat ini. Kirim CV Anda untuk masuk daftar tunggu kami.</p>
        @else
            <div class="space-y-6">
                @foreach ($jobOpenings as $job)
                    <x-sections.job-card :job="$job" />
                @endforeach
            </div>
        @endif
    </section>

    <x-sections.cta-band
        title="Tidak menemukan posisi yang cocok?"
        description="Kirimkan CV Anda ke tim kami — kami akan menghubungi Anda saat ada posisi yang sesuai."
        button-label="Hubungi Kami"
    />
@endsection
