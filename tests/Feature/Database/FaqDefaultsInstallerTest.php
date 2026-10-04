<?php

namespace Tests\Feature\Database;

use App\Enums\FaqPlacement;
use App\Models\FaqItem;
use App\Support\PageContent\PageContentInstaller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqDefaultsInstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_installer_plants_three_product_and_three_contact_faqs_with_the_original_texts(): void
    {
        $product = FaqItem::query()->forPlacement(FaqPlacement::Product)->orderBy('order')->get();
        $contact = FaqItem::query()->forPlacement(FaqPlacement::Contact)->orderBy('order')->get();

        $this->assertSame(
            ['Berapa lama garansi panel?', 'Apakah bisa custom kapasitas?', 'Bagaimana proses instalasinya?'],
            $product->pluck('question')->all(),
        );
        $this->assertSame(
            ['Setelah kirim pesan, apa langkah selanjutnya?', 'Apakah survei lokasi berbayar?', 'Bisa konsultasi tanpa datang ke kantor?'],
            $contact->pluck('question')->all(),
        );
        $this->assertStringStartsWith('Panel surya SUOER dilengkapi dengan garansi kinerja linier hingga 25 tahun', $product->first()->answer);
        $this->assertTrue($product->every->is_active);
    }

    public function test_installer_is_idempotent(): void
    {
        PageContentInstaller::install();
        PageContentInstaller::install();

        $this->assertSame(3, FaqItem::query()->forPlacement(FaqPlacement::Product)->count());
        $this->assertSame(3, FaqItem::query()->forPlacement(FaqPlacement::Contact)->count());
    }

    public function test_installer_never_overwrites_or_refills_a_placement_the_admin_has_touched(): void
    {
        FaqItem::query()->forPlacement(FaqPlacement::Product)->first()->update(['answer' => 'Jawaban admin.']);
        FaqItem::query()->forPlacement(FaqPlacement::Product)->orderBy('order')->skip(1)->take(2)->get()->each->delete();

        PageContentInstaller::install();

        $remaining = FaqItem::query()->forPlacement(FaqPlacement::Product)->get();
        $this->assertCount(1, $remaining);
        $this->assertSame('Jawaban admin.', $remaining->first()->answer);
    }

    public function test_pre_existing_faq_rows_default_to_the_faq_page_and_active(): void
    {
        $legacy = FaqItem::query()->create(['question' => 'Lama?', 'answer' => 'Ya.', 'order' => 0]);

        $legacy->refresh();

        $this->assertSame(FaqPlacement::Faq, $legacy->placement);
        $this->assertTrue($legacy->is_active);
    }

    public function test_demo_seeder_is_not_required_for_the_defaults(): void
    {
        $this->assertSame(0, FaqItem::query()->forPlacement(FaqPlacement::Faq)->count());
    }
}
