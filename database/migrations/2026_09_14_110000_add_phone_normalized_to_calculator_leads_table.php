<?php

use App\Models\CalculatorLead;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor telepon dinormalkan (hanya digit, prefix 62) supaya lead dari orang
 * yang sama bisa dikenali walau formatnya berbeda-beda: "0812-3456-7890",
 * "+62 812 3456 7890", dan "6281234567890" semuanya jadi kunci yang sama.
 * Dipakai untuk deteksi duplikat & rate limit per nomor di
 * CalculatorController.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculator_leads', function (Blueprint $table) {
            $table->string('phone_normalized')->nullable()->after('phone')->index();
        });

        // Isi baris yang sudah terlanjur masuk sebelum kolom ini ada.
        CalculatorLead::query()->whereNull('phone_normalized')->cursor()
            ->each(function (CalculatorLead $lead) {
                $lead->updateQuietly([
                    'phone_normalized' => CalculatorLead::normalizePhone($lead->phone),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('calculator_leads', function (Blueprint $table) {
            $table->dropIndex(['phone_normalized']);
            $table->dropColumn('phone_normalized');
        });
    }
};
