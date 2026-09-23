{{--
    Kontrol pengindeksan, kode verifikasi mesin pencari, dan meta tag
    tambahan bebas (FR-030, FR-033, FR-034, spec 023-site-settings). $seo
    disediakan oleh layouts/public.blade.php.
--}}
@php
    $robotsDirectives = array_filter([
        $seo->allow_indexing ? null : 'noindex',
        $seo->allow_following ? null : 'nofollow',
    ]);
@endphp
@if (filled($robotsDirectives))
    <meta name="robots" content="{{ implode(', ', $robotsDirectives) }}">
@endif

@if ($seo->verification_google)
    <meta name="google-site-verification" content="{{ $seo->verification_google }}">
@endif
@if ($seo->verification_bing)
    <meta name="msvalidate.01" content="{{ $seo->verification_bing }}">
@endif
@if ($seo->verification_yandex)
    <meta name="yandex-verification" content="{{ $seo->verification_yandex }}">
@endif
@if ($seo->verification_baidu)
    <meta name="baidu-site-verification" content="{{ $seo->verification_baidu }}">
@endif

{{-- Meta tag tambahan bebas dimuat apa adanya (FR-033) — sengaja tidak
     di-escape agar admin bisa menulis tag <meta>/<link> yang sah, bukan
     kelalaian. Berbeda dari Scripts & Analytics (FR-045), halaman SEO tidak
     dibatasi super_admin — hanya pengguna yang sudah punya akses panel admin
     yang bisa mengisinya, konsisten dengan tingkat kepercayaan field SEO
     lain di halaman yang sama. --}}
@if ($seo->additional_head_meta)
    {!! $seo->additional_head_meta !!}
@endif
