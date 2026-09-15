<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sepuluh kolom baru mengangkat Banner dari "gambar promosi" menjadi
     * slide hero utuh (data-model.md). Seluruhnya nullable kecuali dua
     * kolom preset yang ber-default, agar baris lama tetap valid.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('badge_text', 120)->nullable()->after('link_url');
            $table->string('heading', 160)->nullable()->after('badge_text');
            $table->text('subheading')->nullable()->after('heading');
            $table->string('cta_primary_label', 60)->nullable()->after('subheading');
            $table->string('cta_primary_url', 255)->nullable()->after('cta_primary_label');
            $table->string('cta_secondary_label', 60)->nullable()->after('cta_primary_url');
            $table->string('cta_secondary_url', 255)->nullable()->after('cta_secondary_label');
            $table->text('trust_html')->nullable()->after('cta_secondary_url');
            $table->string('overlay_style', 16)->default('dark')->after('trust_html');
            $table->string('text_position', 16)->default('left')->after('overlay_style');
        });

        $this->backfillHeroContent();
    }

    /**
     * Salin teks hero yang berlaku sekarang ke banner ber-`order` terkecil
     * (pemecah seri `id` terkecil), hanya bila `heading` masih kosong
     * (research.md R10, FR-016). Teks disalin sebagai literal agar hasil
     * migrasi deterministik dan tidak bergantung pada isi berkas Blade.
     */
    private function backfillHeroContent(): void
    {
        $target = DB::table('banners')
            ->orderBy('order')
            ->orderBy('id')
            ->first();

        if ($target === null || $target->heading !== null) {
            return;
        }

        DB::table('banners')->where('id', $target->id)->update([
            'badge_text' => 'Solar Panel Terpercaya • Efisiensi Hingga 80%',
            'heading' => 'Nyalakan Rumah & Bisnis Anda dengan Energi Matahari',
            'subheading' => 'Solusi tata surya terdepan untuk efisiensi maksimal dan investasi jangka panjang tanpa mengorbankan estetika hunian Anda.',
            'cta_primary_label' => 'Konsultasi Gratis',
            'cta_primary_url' => '/kontak',
            'cta_secondary_label' => 'Pelajari Cara Kerja',
            'cta_secondary_url' => '/#kalkulator',
            'trust_html' => '<div class="flex items-center gap-2"><span>✓</span><span>★</span><span>✦</span></div><p><strong>500+ Pelanggan Puas</strong></p><p><small>Terpasang di seluruh wilayah Indonesia</small></p>',
            'overlay_style' => 'dark',
            'text_position' => 'left',
        ]);
    }

    /**
     * Rollback membuang kesepuluh kolom. Konten teks hilang saat rollback —
     * konsekuensi yang diterima dan didokumentasikan di quickstart (R10).
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn([
                'badge_text',
                'heading',
                'subheading',
                'cta_primary_label',
                'cta_primary_url',
                'cta_secondary_label',
                'cta_secondary_url',
                'trust_html',
                'overlay_style',
                'text_position',
            ]);
        });
    }
};
