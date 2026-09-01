@php
    $brand = app(\App\Settings\BrandSettings::class);
    $appName = $brand->app_name ?: config('app.name');

    $navLinks = [
        ['label' => 'Beranda', 'href' => url('/')],
        ['label' => 'Tentang Kami', 'href' => url('/tentang-kami')],
        ['label' => 'Produk', 'href' => url('/produk')],
        ['label' => 'Kontak', 'href' => url('/kontak')],
    ];
@endphp
<header
    x-data="{ mobileOpen: false, scrolled: false }"
    x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 50)"
    :class="scrolled ? 'py-2' : 'py-4'"
    class="fixed top-0 w-full z-50 transition-all duration-300 bg-surface/90 backdrop-blur"
>
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
        <div class="flex items-center space-x-12">
            <a href="{{ url('/') }}" class="font-headline-lg text-headline-lg font-extrabold text-primary tracking-tight">
                {{ $appName }}
            </a>
            <nav class="hidden md:flex space-x-8">
                @foreach ($navLinks as $link)
                    <a href="{{ $link['href'] }}" class="font-medium text-primary hover:text-primary-container transition-colors">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="hidden md:block">
            <a href="{{ url('/kontak') }}" class="btn-fill text-white px-6 py-3 font-medium hover:-translate-y-0.5 transition-transform inline-block shadow-md bg-primary-container rounded-lg">
                Konsultasi Gratis
            </a>
        </div>

        <button type="button" class="md:hidden text-primary" @click="mobileOpen = !mobileOpen" aria-label="Buka menu navigasi">
            <span class="material-symbols-outlined text-3xl" x-text="mobileOpen ? 'close' : 'menu'">menu</span>
        </button>
    </div>

    <nav x-show="mobileOpen" x-cloak x-transition class="md:hidden flex flex-col gap-4 px-6 py-6 bg-surface">
        @foreach ($navLinks as $link)
            <a href="{{ $link['href'] }}" class="font-medium text-primary">{{ $link['label'] }}</a>
        @endforeach
        <a href="{{ url('/kontak') }}" class="bg-primary-container text-white px-6 py-3 rounded-lg text-center font-medium">
            Konsultasi Gratis
        </a>
    </nav>
</header>
