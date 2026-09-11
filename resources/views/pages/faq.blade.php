@extends('layouts.public')

@php
    $appName = app(\App\Settings\BrandSettings::class)->app_name ?: config('app.name');
@endphp

@section('title', 'FAQ — '.$appName)
@section('meta_description', 'Pertanyaan yang sering diajukan seputar produk dan layanan '.$appName.'.')

@section('content')
    <div
        x-data="{
            cat: 'all',
            q: '',
            open: null,
            items: {{ \Illuminate\Support\Js::from($faqItems->map(fn ($item) => ['id' => $item->id, 'category' => $item->category])) }},
            searchText(id) {
                return (this.$refs['item-' + id]?.dataset.search ?? '');
            },
            get filtered() {
                const q = this.q.trim().toLowerCase();
                return this.items.filter((item) =>
                    (this.cat === 'all' || item.category === this.cat)
                    && (q === '' || this.searchText(item.id).includes(q))
                );
            },
            isVisible(id) {
                return this.filtered.some((item) => item.id === id);
            },
        }"
    >
        {{-- Hero --}}
        <section class="relative px-6 pt-40 pb-16 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-surface-container to-background -z-10"></div>
            <div class="max-w-3xl mx-auto text-center">
                <nav class="flex justify-center text-sm text-outline mb-6">
                    <ol class="flex items-center gap-2">
                        <li><a class="hover:text-primary transition-colors" href="{{ url('/') }}">Beranda</a></li>
                        <li class="flex items-center"><span class="material-symbols-outlined text-base">chevron_right</span></li>
                        <li class="text-primary font-semibold">FAQ</li>
                    </ol>
                </nav>
                <h1 class="font-headline-xl text-4xl md:text-5xl font-extrabold mb-4 text-primary-container">Pertanyaan Umum</h1>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl mx-auto mb-8">
                    Temukan jawaban cepat seputar layanan, instalasi, dan produk panel surya kami.
                </p>
                <div class="max-w-xl mx-auto relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">search</span>
                    <input
                        type="text"
                        x-model="q"
                        placeholder="Cari pertanyaan..."
                        aria-label="Cari pertanyaan"
                        class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg py-3 pl-12 pr-4 shadow-sm font-body-md text-body-md focus:outline-none focus:border-primary-container focus:ring-2 focus:ring-primary-container/30 transition-all"
                    >
                </div>
            </div>
        </section>

        @if ($faqItems->isEmpty())
            <section class="px-6 max-w-3xl mx-auto py-24">
                <p class="text-center text-on-surface-variant">Belum ada pertanyaan yang tersedia saat ini.</p>
            </section>
        @else
            <section class="reveal-element px-6 max-w-7xl mx-auto py-16 grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                @if ($categories->count() > 1)
                    {{-- Sidebar kategori (desktop) --}}
                    <aside class="hidden md:flex flex-col gap-1 md:col-span-3 md:sticky md:top-28">
                        <button type="button" @click="cat = 'all'"
                                :class="cat === 'all' ? 'bg-primary-container/10 text-primary-container font-label-bold text-label-bold' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary'"
                                class="text-left px-3 py-2.5 rounded-lg transition-colors font-body-md text-body-md">
                            Semua Kategori
                        </button>
                        @foreach ($categories as $category)
                            <button type="button" @click="cat = @js($category)"
                                    :class="cat === @js($category) ? 'bg-primary-container/10 text-primary-container font-label-bold text-label-bold' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary'"
                                    class="text-left px-3 py-2.5 rounded-lg transition-colors font-body-md text-body-md">
                                {{ $category }}
                            </button>
                        @endforeach
                    </aside>

                    {{-- Pills kategori (mobile) --}}
                    <div class="md:hidden flex overflow-x-auto pb-3 gap-2 [&::-webkit-scrollbar]:hidden">
                        <button type="button" @click="cat = 'all'"
                                :class="cat === 'all' ? 'bg-primary-container/10 text-primary-container border-primary-container/20' : 'bg-surface-container-lowest text-on-surface-variant border-outline-variant/50'"
                                class="whitespace-nowrap px-4 py-1.5 rounded-full font-label-bold text-label-bold border transition-colors">
                            Semua
                        </button>
                        @foreach ($categories as $category)
                            <button type="button" @click="cat = @js($category)"
                                    :class="cat === @js($category) ? 'bg-primary-container/10 text-primary-container border-primary-container/20' : 'bg-surface-container-lowest text-on-surface-variant border-outline-variant/50'"
                                    class="whitespace-nowrap px-4 py-1.5 rounded-full font-label-bold text-label-bold border transition-colors">
                                {{ $category }}
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Daftar accordion --}}
                <div @class([
                    'flex flex-col gap-3',
                    'md:col-span-9' => $categories->count() > 1,
                    'md:col-span-12 max-w-3xl mx-auto w-full' => $categories->count() <= 1,
                ])>
                    @foreach ($faqItems as $item)
                        <div
                            x-ref="item-{{ $item->id }}"
                            data-search="{{ \Illuminate\Support\Str::lower($item->question.' '.$item->answer) }}"
                            x-show="isVisible({{ $item->id }})"
                            class="bg-white border border-outline-variant/40 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden"
                        >
                            <button type="button" @click="open = open === {{ $item->id }} ? null : {{ $item->id }}"
                                    class="w-full flex items-center justify-between gap-4 p-6 text-left"
                                    :aria-expanded="open === {{ $item->id }}">
                                <h3 class="font-headline-lg text-body-md font-semibold transition-colors"
                                    :class="open === {{ $item->id }} ? 'text-primary' : 'text-on-surface'">{{ $item->question }}</h3>
                                <span class="material-symbols-outlined text-primary transition-transform shrink-0" :class="open === {{ $item->id }} && 'rotate-180'">expand_more</span>
                            </button>
                            <div x-show="open === {{ $item->id }}" x-cloak x-transition class="px-6 pb-6 -mt-1">
                                <p class="font-body-md text-body-md text-on-surface-variant">{{ $item->answer }}</p>
                            </div>
                        </div>
                    @endforeach

                    <p x-show="filtered.length === 0" x-cloak class="text-center text-on-surface-variant py-12">
                        Tidak ada pertanyaan yang cocok dengan pencarian Anda.
                    </p>
                </div>
            </section>
        @endif
    </div>

    <x-sections.cta-band
        title="Masih ada pertanyaan lain?"
        subtitle="Tim kami siap membantu menjawab kebutuhan spesifik Anda"
        button-label="Hubungi Kami"
        :button-href="url('/kontak')"
        button-icon="forum"
    />
@endsection

@push('head')
    <x-seo.json-ld :schema="$schema" />
@endpush
