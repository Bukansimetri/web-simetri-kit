{{--
    Meng-echo CSS custom property yang bisa di-override Theme Settings
    (FR-002, FR-005). Fallback ke default Luminous Azure bila admin belum
    mengisi Brand/Theme Settings — lihat App\Settings\BrandSettings.
--}}
@php
    $brand = app(\App\Settings\BrandSettings::class);
@endphp
<style>
    :root {
        --brand-color-primary: {{ $brand->primary_color ?: \App\Settings\BrandSettings::DEFAULT_PRIMARY_COLOR }};
        --brand-color-secondary: {{ $brand->secondary_color ?: \App\Settings\BrandSettings::DEFAULT_SECONDARY_COLOR }};
        --brand-font-heading: '{{ $brand->font_heading ?: \App\Settings\BrandSettings::DEFAULT_FONT_HEADING }}';
        --brand-font-body: '{{ $brand->font_body ?: \App\Settings\BrandSettings::DEFAULT_FONT_BODY }}';
    }
</style>
