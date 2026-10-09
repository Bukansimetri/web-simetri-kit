<?php

namespace Tests\Feature\Database;

use App\Models\ContactTopic;
use Database\Seeders\ContactTopicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTopicSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_restores_default_topics_without_duplicates(): void
    {
        ContactTopic::query()->delete();

        $this->seed(ContactTopicSeeder::class);
        $this->seed(ContactTopicSeeder::class);

        $this->assertSame(
            ['umum', 'residensial', 'komersial', 'pompa'],
            ContactTopic::query()->ordered()->pluck('slug')->all(),
        );
    }

    public function test_seeder_does_not_overwrite_admin_changes(): void
    {
        ContactTopic::query()->where('slug', 'umum')->update(['name' => 'Tanya Apa Saja', 'is_active' => false]);

        $this->seed(ContactTopicSeeder::class);

        $this->assertDatabaseHas('contact_topics', ['slug' => 'umum', 'name' => 'Tanya Apa Saja', 'is_active' => false]);
    }
}
