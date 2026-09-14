<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lead dari form "Hitung Estimasi Penghematan" (home). Terpisah dari
 * `contact_submissions` karena inputnya terstruktur (metode, tagihan,
 * peralatan) dan hasil hitungannya perlu disimpan sebagai data, bukan teks
 * bebas, supaya bisa difilter, dilaporkan, dan angkanya konsisten dengan
 * yang dilihat pelanggan (snapshot `assumptions` per lead).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculator_leads', function (Blueprint $table) {
            $table->id();

            // Identitas pelanggan.
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('area')->nullable();

            // Input mentah dari pelanggan.
            $table->string('category'); // residential | industrial
            $table->string('method'); // bill | appliance
            $table->unsignedBigInteger('monthly_bill')->nullable();
            $table->string('va_capacity')->nullable();
            $table->json('appliances')->nullable();
            $table->unsignedInteger('total_watt')->nullable();

            // Hasil perhitungan (dihitung server, lihat SavingsEstimator).
            $table->unsignedBigInteger('estimated_monthly_bill');
            $table->unsignedBigInteger('savings_year1');
            $table->unsignedBigInteger('total_savings_25y');
            $table->unsignedBigInteger('estimated_investment');
            $table->decimal('breakeven_years', 4, 1);
            $table->decimal('annual_kwh', 10, 1);
            $table->json('assumptions');

            // Follow-up sales.
            $table->string('status')->default('new');
            $table->text('follow_up_notes')->nullable();
            $table->foreignId('followed_up_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('followed_up_at')->nullable();

            // Jejak teknis untuk evaluasi kanal marketing.
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->json('utm')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_leads');
    }
};
