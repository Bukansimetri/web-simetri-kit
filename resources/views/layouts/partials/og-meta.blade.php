{{--
    Meta tag Open Graph (FR-010). $brand disediakan oleh layouts/public.blade.php.
    Fallback ke asset default Luminous Azure lewat BrandSettings::ogImageUrl()
    saat admin belum mengupload OG image sendiri.
--}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:title" content="@yield('og_title', $appName)">
<meta property="og:description" content="@yield('meta_description', 'Solusi panel surya untuk rumah, bisnis, dan industri.')">
<meta property="og:image" content="@yield('og_image', $brand->ogImageUrl())">
