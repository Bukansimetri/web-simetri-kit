<?php

namespace Tests\Feature\Pages;

use App\Models\FaqItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_page_lists_items_in_order(): void
    {
        FaqItem::factory()->create(['question' => 'Pertanyaan Kedua', 'order' => 2]);
        FaqItem::factory()->create(['question' => 'Pertanyaan Pertama', 'order' => 1]);

        $response = $this->get('/faq');

        $response->assertOk();
        $response->assertSeeInOrder(['Pertanyaan Pertama', 'Pertanyaan Kedua'], escape: false);
    }
}
