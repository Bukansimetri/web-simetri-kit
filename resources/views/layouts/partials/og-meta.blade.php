{{--
    Meta tag Open Graph + Twitter Card (AMC-223 FR-001). $brand & $appName
    disediakan oleh layouts/public.blade.php. Fallback deskripsi ke
    BrandSettings::meta_description (lalu ke string default bawaan), dan
    gambar ke BrandSettings::ogImageUrl() — berlaku otomatis untuk halaman
    yang belum pernah disentuh admin (FR-003/FR-013).
--}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:title" content="@yield('og_title', $appName)">
<meta property="og:description" content="@yield('meta_description', $brand->meta_description ?: 'Solusi panel surya untuk rumah, bisnis, dan industri.')">
<meta property="og:image" content="@yield('og_image', $brand->ogImageUrl())">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('og_title', $appName)">
<meta name="twitter:description" content="@yield('meta_description', $brand->meta_description ?: 'Solusi panel surya untuk rumah, bisnis, dan industri.')">
<meta name="twitter:image" content="@yield('og_image', $brand->ogImageUrl())">
