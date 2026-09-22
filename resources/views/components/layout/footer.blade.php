@php
    $brand = app(\App\Settings\BrandSettings::class);
    $appName = $brand->app_name ?: config('app.name');
    $logoUrl = filled($brand->logo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($brand->logo_path)
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($brand->logo_path)
        : null;

    // Menu Builder (spec 017-menu-builder) adalah sumber utama kolom footer.
    // Item induk (punya sub-item) jadi judul kolom; item tanpa sub-item
    // dikelompokkan ke kolom "Lainnya". Kolom statis di bawah hanya dipakai
    // sebagai fallback bila admin belum mengisi item apa pun di lokasi
    // 'footer'.
    $builderFooterItems = \App\Models\MenuItem::treeForLocation('footer');

    if ($builderFooterItems->isNotEmpty()) {
        $footerColumns = [];

        foreach ($builderFooterItems as $item) {
            if (! empty($item['children'])) {
                // href null (tanpa tautan / target terhapus, FR-011) tetap
                // tampil sebagai label, diarahkan ke '#' alih-alih disembunyikan.
                $footerColumns[$item['label']] = array_map(
                    fn (array $child) => ['label' => $child['label'], 'href' => $child['href'] ?? '#'],
                    $item['children']
                );

                continue;
            }

            $footerColumns['Lainnya'][] = ['label' => $item['label'], 'href' => $item['href'] ?? '#'];
        }
    } else {
        $footerColumns = [
            'Solusi' => [
                ['label' => 'Panel Residensial', 'href' => url('/produk')],
                ['label' => 'B2B & Industri', 'href' => url('/produk')],
                ['label' => 'Pompa Air Surya', 'href' => url('/produk')],
                ['label' => 'Net Metering PLN', 'href' => url('/produk')],
            ],
            'Perusahaan' => array_values(array_filter([
                ['label' => 'Tentang Kami', 'href' => url('/tentang-kami')],
                ['label' => 'Blog & Artikel', 'href' => url('/artikel')],
                $brand->career_module_enabled ? ['label' => 'Karir', 'href' => url('/karir')] : null,
                ['label' => 'FAQ', 'href' => url('/faq')],
            ])),
        ];
    }
@endphp
<footer class="reveal-element bg-on-background pt-20 pb-10 px-6 border-t border-primary/40">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
        <div>
            <div class="mb-6">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-10 w-auto object-contain">
                @else
                    <div class="font-headline-lg text-headline-lg font-extrabold text-3xl text-primary-container tracking-tight">
                        {{ $appName }}
                    </div>
                @endif
            </div>
            <p class="text-white/60 text-sm leading-relaxed">
                Menginspirasi masa depan berkelanjutan melalui inovasi tenaga surya yang elegan dan presisi tinggi untuk masyarakat Indonesia.
            </p>
        </div>

        @foreach ($footerColumns as $title => $links)
            <div>
                <h4 class="font-bold text-white mb-6">{{ $title }}</h4>
                <ul class="space-y-4 text-white/60 text-sm">
                    @foreach ($links as $link)
                        <li>
                            <a class="hover:text-primary-container hover:translate-x-1 inline-block transition-all" href="{{ $link['href'] }}">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div>
            <h4 class="font-bold text-white mb-6">Kontak</h4>
            <ul class="space-y-4 text-white/60 text-sm">
                <li class="flex items-start"><span class="material-symbols-outlined text-primary-container text-lg mr-2">location_on</span> Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan 12190</li>
                <li class="flex items-center"><span class="material-symbols-outlined text-primary-container text-lg mr-2">mail</span> hello@suoer.id</li>
                <li class="flex items-center"><span class="material-symbols-outlined text-primary-container text-lg mr-2">call</span> (021) 555-0123</li>
            </ul>
        </div>
    </div>

    <div class="max-w-7xl mx-auto pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-white/40">
        <p>&copy; {{ now()->year }} {{ $appName }} Solar Energy. All Rights Reserved.</p>
        <div class="flex gap-6">
            <a class="hover:text-white transition-colors" href="{{ url('/halaman/kebijakan-privasi') }}">Kebijakan Privasi</a>
            <a class="hover:text-white transition-colors" href="{{ url('/halaman/syarat-ketentuan') }}">Syarat &amp; Ketentuan</a>
        </div>
    </div>
</footer>
