{{--
    Membungkus kode di dalam slot menjadi inert ketika kategorinya butuh
    persetujuan (FR-054, contracts/consent-gating-contract.md §5). Kategori
    `none` dirender apa adanya seperti biasa. `<template>` dipilih daripada
    `<script type="text/plain">` karena isinya bisa berupa HTML campuran
    (mis. cuplikan GTM: <script> + <noscript><iframe>), dan hanya <template>
    yang menjaga struktur itu tetap utuh sebagai node DOM inert — lihat
    resources/js/cookie-consent.js untuk logika aktivasinya.
--}}
@props(['category'])

@if ($category === \App\Settings\ScriptSettings::CONSENT_NONE)
    {{ $slot }}
@else
    <template data-consent-category="{{ $category }}">{{ $slot }}</template>
@endif
