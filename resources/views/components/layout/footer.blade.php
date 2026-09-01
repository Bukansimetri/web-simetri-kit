@php
    $brand = app(\App\Settings\BrandSettings::class);
    $appName = $brand->app_name ?: config('app.name');

    $footerColumns = [
        'Solusi' => [
            ['label' => 'Panel Residensial', 'href' => url('/produk')],
            ['label' => 'B2B & Industri', 'href' => url('/produk')],
            ['label' => 'Pompa Air Surya', 'href' => url('/produk')],
        ],
        'Perusahaan' => [
            ['label' => 'Tentang Kami', 'href' => url('/tentang-kami')],
            ['label' => 'Blog & Artikel', 'href' => url('/artikel')],
            ['label' => 'Karir', 'href' => url('/karir')],
            ['label' => 'FAQ', 'href' => url('/faq')],
        ],
    ];
@endphp
<footer class="pt-20 pb-10 px-6 border-t border-white/10" style="background-color: var(--color-on-background);">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
        <div class="col-span-1 md:col-span-1">
            <div class="font-headline-lg text-headline-lg font-extrabold text-3xl text-white mb-6 tracking-tight">
                {{ $appName }}
            </div>
            <p class="text-white/60 text-sm leading-relaxed mb-6">
                Menginspirasi masa depan berkelanjutan melalui inovasi tenaga surya yang elegan dan presisi tinggi untuk masyarakat Indonesia.
            </p>
        </div>

        @foreach ($footerColumns as $title => $links)
            <div>
                <h4 class="font-bold text-white mb-6">{{ $title }}</h4>
                <ul class="space-y-4 text-white/60 text-sm">
                    @foreach ($links as $link)
                        <li><a class="hover:text-primary-fixed transition-colors" href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div>
            <h4 class="font-bold text-white mb-6">Kontak</h4>
            <ul class="space-y-4 text-white/60 text-sm">
                <li class="flex items-start"><span class="material-symbols-outlined text-primary-fixed text-lg mr-2">location_on</span> Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan 12190</li>
                <li class="flex items-center"><span class="material-symbols-outlined text-primary-fixed text-lg mr-2">mail</span> hello@suoer.id</li>
                <li class="flex items-center"><span class="material-symbols-outlined text-primary-fixed text-lg mr-2">call</span> (021) 555-0123</li>
            </ul>
        </div>
    </div>

    <div class="max-w-7xl mx-auto pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center text-xs text-white/40">
        <p>&copy; {{ now()->year }} {{ $appName }}. All Rights Reserved.</p>
        <div class="flex space-x-6 mt-4 md:mt-0">
            <a class="hover:text-white transition-colors" href="{{ url('/tentang-kami') }}">Kebijakan Privasi</a>
            <a class="hover:text-white transition-colors" href="{{ url('/tentang-kami') }}">Syarat &amp; Ketentuan</a>
        </div>
    </div>
</footer>
