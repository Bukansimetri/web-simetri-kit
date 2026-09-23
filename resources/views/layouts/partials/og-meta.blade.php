{{--
    Meta tag Open Graph + Twitter Card (AMC-223 FR-001). $appName, $seo, dan
    $social disediakan oleh layouts/public.blade.php. Fallback deskripsi ke
    SeoSettings::default_meta_description (lalu ke string default bawaan),
    dan gambar ke SocialSettings::ogImageUrl() — berlaku otomatis untuk
    halaman yang belum pernah disentuh admin (FR-003/FR-013).
--}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:title" content="@yield('og_title', $appName)">
<meta property="og:description" content="@yield('meta_description', $seo->default_meta_description ?: 'Solusi panel surya untuk rumah, bisnis, dan industri.')">
<meta property="og:image" content="@yield('og_image', $social->ogImageUrl())">

<meta name="twitter:card" content="summary_large_image">
@if ($seo->twitter_handle)
    <meta name="twitter:site" content="{{ $seo->twitter_handle }}">
@endif
<meta name="twitter:title" content="@yield('og_title', $appName)">
<meta name="twitter:description" content="@yield('meta_description', $seo->default_meta_description ?: 'Solusi panel surya untuk rumah, bisnis, dan industri.')">
<meta name="twitter:image" content="@yield('og_image', $social->ogImageUrl())">
