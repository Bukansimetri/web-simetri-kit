{{--
    Meng-echo CSS custom property yang bisa di-override Tampilan (FR-002,
    FR-005). Fallback ke default Luminous Azure bila admin belum mengisi
    Tampilan — lihat App\Settings\AppearanceSettings.
--}}
@php
    $appearance = app(\App\Settings\AppearanceSettings::class);
@endphp
<style>
    :root {
        --brand-color-primary: {{ $appearance->primary_color ?: \App\Settings\AppearanceSettings::DEFAULT_PRIMARY_COLOR }};
        --brand-color-secondary: {{ $appearance->secondary_color ?: \App\Settings\AppearanceSettings::DEFAULT_SECONDARY_COLOR }};
        --brand-font-heading: '{{ $appearance->font_heading ?: \App\Settings\AppearanceSettings::DEFAULT_FONT_HEADING }}';
        --brand-font-body: '{{ $appearance->font_body ?: \App\Settings\AppearanceSettings::DEFAULT_FONT_BODY }}';
    }
</style>
