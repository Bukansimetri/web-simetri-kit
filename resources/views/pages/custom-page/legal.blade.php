@extends('layouts.public')

@php
    use App\Support\PageContent\PageContent;
    use Illuminate\Support\Facades\Storage;

    $site = app(\App\Settings\SiteSettings::class);
    $legal = $customPage->legal ?? [];
    $sections = collect($legal['sections'] ?? [])->filter(fn ($section) => filled($section['title'] ?? null))->values();

    $pdfUrl = filled($legal['pdf_path'] ?? null) && Storage::disk('public')->exists($legal['pdf_path'])
        ? Storage::disk('public')->url($legal['pdf_path'])
        : null;

    $whatsappUrl = filled($legal['contact_whatsapp_label'] ?? null)
        ? $site->whatsappUrl((string) ($legal['contact_whatsapp_message'] ?? ''))
        : null;
    $contactEmail = $legal['contact_email'] ?? null;
    $hasContactBox = filled($legal['contact_title'] ?? null) || filled($legal['contact_text'] ?? null);

    $ctaUrl = trim((string) ($legal['cta_button_url'] ?? ''));
    $ctaUrl = match (true) {
        $ctaUrl === '' => url('/kontak'),
        str_starts_with($ctaUrl, '/') => url($ctaUrl),
        (bool) preg_match('#^(https?://|mailto:|tel:)#i', $ctaUrl) => $ctaUrl,
        default => url('/kontak'),
    };

    $richClasses = 'text-on-surface-variant leading-relaxed [&_p]:mt-4 [&_p:first-child]:mt-0 [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-2 [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:space-y-2 [&_strong]:text-on-surface [&_a]:text-primary [&_a]:underline';
@endphp

@section('title', \App\Support\Seo\PageTitle::forContent('halaman', $customPage->meta_title, $customPage->title))
@section('meta_description', $customPage->seoDescription())
@section('og_title', $customPage->seoTitle())
@section('og_image', $customPage->seoImageUrl() ?? app(\App\Settings\SocialSettings::class)->ogImageUrl())

@section('content')
    <x-sections.page-hero
        :title="$customPage->title"
        :subtitle="$legal['subtitle'] ?? null"
        :breadcrumb="$customPage->title"
        :image="PageContent::imageUrl($legal['hero_image_path'] ?? null, 'images/mockup/tentang-kami-3.jpg')"
    />

    <div
        class="max-w-7xl mx-auto px-6 py-12 md:py-16"
        x-data="{
            active: 1,
            init() {
                if (! ('IntersectionObserver' in window)) { return; }
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) { this.active = Number(entry.target.dataset.index); }
                    });
                }, { rootMargin: '-30% 0px -60% 0px' });
                this.$root.querySelectorAll('[data-legal-section]').forEach((el) => observer.observe(el));
            },
        }"
    >
        <div class="grid grid-cols-1 lg:grid-cols-[300px_minmax(0,1fr)] gap-8 items-start">
            <aside class="space-y-6 lg:sticky lg:top-28">
                @if ($sections->isNotEmpty())
                    <nav class="bg-white rounded-2xl border border-outline-variant/40 p-6 shadow-sm" aria-label="Daftar isi">
                        <div class="flex items-center gap-2 border-b border-outline-variant/40 pb-4 mb-4">
                            <span class="material-symbols-outlined text-primary">format_list_bulleted</span>
                            <h2 class="text-lg font-bold text-on-surface tracking-tight">Daftar Isi</h2>
                        </div>
                        <ul class="flex flex-col gap-1 text-sm font-medium">
                            @foreach ($sections as $index => $section)
                                <li>
                                    <a
                                        href="#bagian-{{ $index + 1 }}"
                                        class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg text-on-surface hover:bg-primary-fixed/40 hover:text-primary transition-colors"
                                        :class="active === {{ $index + 1 }} ? 'bg-primary-fixed/40 text-primary font-semibold' : ''"
                                        :aria-current="active === {{ $index + 1 }} ? 'true' : null"
                                    >
                                        <span class="truncate">{{ $index + 1 }}. {{ $section['title'] }}</span>
                                        <span class="material-symbols-outlined text-base opacity-60">chevron_right</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        @if ($pdfUrl)
                            <a href="{{ $pdfUrl }}" download class="mt-5 flex items-center justify-center gap-2 w-full rounded-xl bg-primary text-white font-semibold px-4 py-3 hover:bg-primary/90 transition-colors">
                                <span class="material-symbols-outlined">download</span>
                                {{ $legal['pdf_label'] ?: 'Unduh Dokumen (PDF)' }}
                            </a>
                        @endif
                    </nav>
                @elseif ($pdfUrl)
                    <a href="{{ $pdfUrl }}" download class="flex items-center justify-center gap-2 w-full rounded-xl bg-primary text-white font-semibold px-4 py-3 hover:bg-primary/90 transition-colors">
                        <span class="material-symbols-outlined">download</span>
                        {{ $legal['pdf_label'] ?: 'Unduh Dokumen (PDF)' }}
                    </a>
                @endif

                @if ($hasContactBox)
                    <div class="rounded-2xl bg-primary-fixed/40 border border-primary-fixed p-6 space-y-4">
                        <span class="material-symbols-outlined text-primary text-3xl">contact_support</span>
                        @if (filled($legal['contact_title'] ?? null))
                            <h3 class="text-lg font-bold text-on-surface">{{ $legal['contact_title'] }}</h3>
                        @endif
                        @if (filled($legal['contact_text'] ?? null))
                            <p class="text-sm text-on-surface-variant leading-relaxed">{{ $legal['contact_text'] }}</p>
                        @endif
                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 w-full rounded-xl bg-[#25D366] text-white font-semibold px-4 py-3 hover:opacity-90 transition-opacity">
                                <span class="material-symbols-outlined">chat</span>
                                {{ $legal['contact_whatsapp_label'] }}
                            </a>
                        @endif
                        @if (filled($contactEmail))
                            <a href="mailto:{{ $contactEmail }}" class="flex items-center justify-center gap-2 text-sm font-medium text-primary hover:underline break-all">
                                <span class="material-symbols-outlined text-base">mail</span>
                                {{ $contactEmail }}
                            </a>
                        @endif
                    </div>
                @endif
            </aside>

            <div class="space-y-8 min-w-0">
                @if (filled($legal['intro'] ?? null))
                    <div class="bg-white rounded-2xl border border-outline-variant/40 p-6 md:p-8 shadow-sm {{ $richClasses }}">
                        {{ PageContent::richText($legal['intro']) }}
                    </div>
                @endif

                @if (filled($legal['highlight_title'] ?? null) || filled($legal['highlight_body'] ?? null))
                    <div class="flex gap-4 rounded-2xl bg-primary-fixed/40 border border-primary-fixed p-6">
                        <span class="material-symbols-outlined text-primary text-3xl shrink-0">verified_user</span>
                        <div>
                            @if (filled($legal['highlight_title'] ?? null))
                                <h3 class="text-lg font-bold text-on-surface">{{ $legal['highlight_title'] }}</h3>
                            @endif
                            @if (filled($legal['highlight_body'] ?? null))
                                <p class="mt-1 text-on-surface-variant leading-relaxed">{{ $legal['highlight_body'] }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach ($sections as $index => $section)
                    @php
                        $cards = collect($section['cards'] ?? [])->filter(fn ($card) => filled($card['title'] ?? null))->values();
                    @endphp
                    <section
                        id="bagian-{{ $index + 1 }}"
                        data-legal-section
                        data-index="{{ $index + 1 }}"
                        class="scroll-mt-28 bg-white rounded-2xl border border-outline-variant/40 p-6 md:p-8 shadow-sm"
                    >
                        <div class="flex items-center gap-4 mb-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-white font-bold">{{ $index + 1 }}</span>
                            <div class="min-w-0">
                                @if (filled($section['label'] ?? null))
                                    <p class="text-xs font-semibold uppercase tracking-widest text-primary/70">Pasal {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }} · {{ $section['label'] }}</p>
                                @endif
                                <h2 class="text-xl md:text-2xl font-bold text-on-surface tracking-tight">{{ $section['title'] }}</h2>
                            </div>
                        </div>

                        @if (filled($section['body'] ?? null))
                            <div class="{{ $richClasses }}">
                                {{ PageContent::richText($section['body']) }}
                            </div>
                        @endif

                        @if ($cards->isNotEmpty())
                            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 {{ $cards->count() % 3 === 0 ? 'lg:grid-cols-3' : '' }} gap-4">
                                @foreach ($cards as $card)
                                    <div class="rounded-xl bg-surface-container-low border border-outline-variant/40 p-5">
                                        @if (filled($card['icon'] ?? null))
                                            <span class="material-symbols-outlined text-primary mb-2">{{ $card['icon'] }}</span>
                                        @endif
                                        <h3 class="text-sm font-bold text-on-surface">{{ $card['title'] }}</h3>
                                        @if (filled($card['text'] ?? null))
                                            <p class="mt-1 text-sm text-on-surface-variant leading-relaxed">{{ $card['text'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (filled($section['note'] ?? null))
                            <p class="mt-5 text-xs italic text-on-surface-variant leading-relaxed">{{ $section['note'] }}</p>
                        @endif
                    </section>
                @endforeach
            </div>
        </div>
    </div>

    @if (filled($legal['cta_title'] ?? null))
        <section class="py-20 px-6 bg-primary text-center">
            <div class="max-w-3xl mx-auto">
                <h2 class="font-headline-xl text-3xl md:text-4xl font-bold text-white leading-tight">{{ $legal['cta_title'] }}</h2>
                @if (filled($legal['cta_body'] ?? null))
                    <p class="mt-4 text-white/90 text-lg">{{ $legal['cta_body'] }}</p>
                @endif
                @if (filled($legal['cta_button_label'] ?? null))
                    <a href="{{ $ctaUrl }}" class="mt-8 inline-flex items-center justify-center gap-3 text-white font-bold px-8 py-4 bg-primary-container hover:bg-primary-container/90 rounded-lg transition-colors">
                        {{ $legal['cta_button_label'] }}
                    </a>
                @endif
            </div>
        </section>
    @endif
@endsection
