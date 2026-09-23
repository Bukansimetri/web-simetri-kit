@php
    $site = app(\App\Settings\SiteSettings::class);
    $appearance = app(\App\Settings\AppearanceSettings::class);
    $appName = $site->site_name ?: config('app.name');
    $logoUrl = filled($appearance->logo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($appearance->logo_path)
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($appearance->logo_path)
        : null;

    // Beranda: header transparan menumpuk di atas hero gelap, jadi solid saat
    // di-scroll. Halaman lain: header putih permanen dengan border bawah.
    $overHero = request()->routeIs('home');

    // Menu Builder (spec 017-menu-builder) adalah sumber utama navbar. Menu
    // statis di bawah hanya dipakai sebagai fallback bila admin belum
    // mengisi item apa pun di lokasi 'navbar-utama'.
    $builderNavItems = \App\Models\MenuItem::treeForLocation('navbar-utama');

    if ($builderNavItems->isNotEmpty()) {
        $navLinks = $builderNavItems->map(fn (array $item) => [
            'label' => $item['label'],
            // href null = tanpa tautan atau tautan internal yang targetnya
            // sudah terhapus (FR-011) — tetap tampil sebagai label, arahkan
            // ke '#' alih-alih menghilangkannya dari navbar.
            'href' => $item['href'] ?? '#',
            'active' => $item['href'] !== null && request()->url() === $item['href'],
        ])->all();
    } else {
        $navLinks = array_filter([
            ['label' => 'Beranda', 'href' => url('/'), 'active' => request()->routeIs('home')],
            ['label' => 'Tentang Kami', 'href' => url('/tentang-kami'), 'active' => request()->routeIs('tentang-kami')],
            ['label' => 'Produk', 'href' => url('/produk'), 'active' => request()->routeIs('produk.*')],
            ['label' => 'Artikel', 'href' => url('/artikel'), 'active' => request()->routeIs('artikel.*')],
            $site->career_module_enabled
                ? ['label' => 'Karir', 'href' => url('/karir'), 'active' => request()->routeIs('karir')]
                : null,
            ['label' => 'Kontak', 'href' => url('/kontak'), 'active' => request()->routeIs('kontak')],
        ]);
    }

    $socials = [
        ['label' => 'Instagram', 'href' => '#', 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.13-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'],
        ['label' => 'Facebook', 'href' => '#', 'path' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
        ['label' => 'YouTube', 'href' => '#', 'path' => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
    ];
@endphp
<header
    x-data="{ open: false, scrolled: {{ $overHero ? 'false' : 'true' }} }"
    @if ($overHero) x-init="scrolled = window.scrollY > 40; window.addEventListener('scroll', () => scrolled = window.scrollY > 40)" @endif
    :class="scrolled ? 'bg-white/90 backdrop-blur-md shadow-sm border-b border-surface-container-low py-3' : 'bg-transparent py-4'"
    class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
>
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-3 items-center">
        <a href="{{ url('/') }}"
           :class="scrolled ? 'text-primary' : '{{ $overHero ? 'text-white' : 'text-primary' }}'"
           class="font-headline-lg text-headline-lg font-extrabold tracking-tight transition-colors flex items-center gap-2">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-9 w-auto object-contain">
            @else
                {{ $appName }}
            @endif
        </a>

        <nav class="hidden md:flex justify-center gap-8">
            @foreach ($navLinks as $link)
                <a href="{{ $link['href'] }}"
                   @class([
                       'text-sm font-medium transition-colors relative whitespace-nowrap',
                       "after:content-[''] after:absolute after:-bottom-1.5 after:left-0 after:w-full after:h-0.5 after:bg-primary-container" => $link['active'],
                   ])
                   :class="scrolled
                       ? '{{ $link['active'] ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-primary' }}'
                       : '{{ $overHero ? 'text-white/90 hover:text-white' : ($link['active'] ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-primary') }}'">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden md:flex items-center justify-end gap-5">
            <div class="flex items-center gap-3">
                @foreach ($socials as $social)
                    <a href="{{ $social['href'] }}" aria-label="{{ $social['label'] }}"
                       :class="scrolled ? 'text-primary hover:text-primary-container' : '{{ $overHero ? 'text-white/80 hover:text-white' : 'text-primary hover:text-primary-container' }}'"
                       class="transition-colors">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="{{ $social['path'] }}" /></svg>
                    </a>
                @endforeach
            </div>
            <a href="{{ url('/kontak') }}" class="btn-fill bg-primary-container text-white text-sm font-medium px-5 py-2.5 rounded-lg shadow-md hover:-translate-y-0.5 transition-transform">
                Konsultasi Gratis
            </a>
        </div>

        <button type="button" @click="open = !open"
                :class="scrolled ? 'text-primary' : '{{ $overHero ? 'text-white' : 'text-primary' }}'"
                class="md:hidden justify-self-end transition-colors" aria-label="Buka menu navigasi">
            <span class="material-symbols-outlined text-3xl" x-text="open ? 'close' : 'menu'">menu</span>
        </button>
    </div>

    <nav x-show="open" x-cloak x-transition class="md:hidden bg-white border-t border-surface-container-low px-6 py-6 flex flex-col gap-4 shadow-sm">
        @foreach ($navLinks as $link)
            <a href="{{ $link['href'] }}" @class(['text-sm font-medium', 'text-primary font-semibold' => $link['active'], 'text-on-surface-variant' => ! $link['active']])>
                {{ $link['label'] }}
            </a>
        @endforeach
        <a href="{{ url('/kontak') }}" class="bg-primary-container text-white text-sm font-medium px-5 py-3 rounded-lg text-center">
            Konsultasi Gratis
        </a>
    </nav>
</header>
