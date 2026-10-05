<?php

namespace Database\Seeders;

use App\Models\JobOpening;
use Illuminate\Database\Seeder;

/**
 * Data contoh (seed) dari mockup public/mockup-html/karir_suoer_header_consistent.
 * Sementara, siap digantikan modul CRUD Epic 3 (AMC-212).
 */
class JobOpeningSeeder extends Seeder
{
    public function run(): void
    {
        $openings = [
            [
                'title' => 'Solar Panel Installation Technician',
                'location' => 'Jakarta & Sekitarnya',
                'employment_type' => 'full-time',
                'description' => '<p>Bertanggung jawab memasang dan memelihara sistem panel surya di lokasi klien residensial &amp; komersial.</p>',
            ],
            [
                'title' => 'Sales Consultant - Solar Energy',
                'location' => 'Jakarta',
                'employment_type' => 'full-time',
                'description' => '<p>Mengedukasi calon klien tentang manfaat energi surya dan menyusun penawaran sistem sesuai kebutuhan mereka.</p>',
            ],
            [
                'title' => 'Magang - Engineering',
                'location' => 'Jakarta / Remote',
                'employment_type' => 'internship',
                'description' => '<p>Membantu tim engineering dalam desain sistem dan dokumentasi teknis proyek instalasi panel surya.</p>',
            ],
        ];

        foreach ($openings as $opening) {
            JobOpening::query()->updateOrCreate(
                ['title' => $opening['title']],
                [
                    'location' => $opening['location'],
                    'employment_type' => $opening['employment_type'],
                    'description' => $opening['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
