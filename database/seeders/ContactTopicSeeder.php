<?php

namespace Database\Seeders;

use App\Models\ContactTopic;
use Illuminate\Database\Seeder;

/**
 * Menanam topik awal form Kontak. Hanya membuat slug yang belum ada, sehingga
 * aman dijalankan ulang dan tidak menimpa perubahan yang sudah dibuat admin.
 */
class ContactTopicSeeder extends Seeder
{
    /**
     * @var array<int, array{slug: string, name: string}>
     */
    private const TOPICS = [
        ['slug' => 'umum', 'name' => 'Konsultasi Umum'],
        ['slug' => 'residensial', 'name' => 'Residensial'],
        ['slug' => 'komersial', 'name' => 'Komersial & Industri'],
        ['slug' => 'pompa', 'name' => 'Pompa Air Tenaga Surya'],
    ];

    public function run(): void
    {
        foreach (self::TOPICS as $index => $topic) {
            ContactTopic::query()->firstOrCreate(
                ['slug' => $topic['slug']],
                ['name' => $topic['name'], 'order' => $index + 1, 'is_active' => true],
            );
        }
    }
}
