<?php

namespace Database\Seeders;

use App\Models\DemoSeedRecord;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

/**
 * Konten demo untuk showcase ke calon klien (AMC-229, spec 019-demo-content-seeder).
 * Dipanggil HANYA lewat `demo:seed` — tidak pernah lewat DatabaseSeeder/
 * app:setup-client (FR-003). Aman dijalankan berulang (research.md #4).
 */
class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        if (DemoSeedRecord::alreadySeeded(TeamMember::class)) {
            return;
        }

        $members = [
            [
                'name' => 'Ir. Andika Pratama',
                'position' => 'Chief Executive Officer',
                'bio' => 'Lebih dari 15 tahun berkecimpung di industri energi terbarukan, memimpin ekspansi solusi panel surya untuk pasar residensial dan komersial di Indonesia.',
                'photo' => 'images/mockup/tentang-kami-2.jpg',
            ],
            [
                'name' => 'Dewi Kartika, S.T.',
                'position' => 'Head of Engineering',
                'bio' => 'Bertanggung jawab atas desain teknis dan standar kualitas instalasi, memastikan setiap sistem terpasang memenuhi standar keselamatan internasional.',
                'photo' => 'images/mockup/tentang-kami-3.jpg',
            ],
            [
                'name' => 'Rangga Saputra',
                'position' => 'Head of Sales & Partnership',
                'bio' => 'Membangun kemitraan dengan pengembang properti dan industri manufaktur untuk memperluas adopsi energi surya di berbagai sektor.',
                'photo' => 'images/mockup/tentang-kami-2.jpg',
            ],
            [
                'name' => 'Maya Anggraini',
                'position' => 'Customer Success Manager',
                'bio' => 'Memastikan setiap klien mendapat pengalaman purna jual terbaik, mulai dari pemantauan performa sistem hingga dukungan teknis berkelanjutan.',
                'photo' => 'images/mockup/tentang-kami-3.jpg',
            ],
        ];

        foreach ($members as $index => $member) {
            $teamMember = TeamMember::create([
                'name' => $member['name'],
                'position' => $member['position'],
                'bio' => $member['bio'],
                'photo_path' => $member['photo'],
                'order' => $index,
                'is_active' => true,
            ]);

            DemoSeedRecord::recordFor($teamMember);
        }
    }
}
