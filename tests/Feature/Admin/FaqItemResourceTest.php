<?php

namespace Tests\Feature\Admin;

use App\Enums\FaqPlacement;
use App\Filament\Resources\FaqItemResource;
use App\Filament\Resources\FaqItemResource\Pages\CreateFaqItem;
use App\Filament\Resources\FaqItemResource\Pages\EditFaqItem;
use App\Filament\Resources\FaqItemResource\Pages\ListFaqItems;
use App\Models\FaqItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FaqItemResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_list_is_filtered_by_placement_with_faq_page_as_default(): void
    {
        $faq = FaqItem::factory()->create(['question' => 'Tanya halaman faq?']);
        $product = FaqItem::factory()->forPlacement(FaqPlacement::Product)->create(['question' => 'Tanya produk?']);

        Livewire::actingAs($this->admin())
            ->test(ListFaqItems::class)
            ->assertCanSeeTableRecords([$faq])
            ->assertCanNotSeeTableRecords([$product])
            ->filterTable('placement', FaqPlacement::Product->value)
            ->assertCanSeeTableRecords([$product])
            ->assertCanNotSeeTableRecords([$faq]);
    }

    public function test_admin_can_create_an_entry_placed_last_within_its_placement(): void
    {
        FaqItem::factory()->forPlacement(FaqPlacement::Product)->create(['order' => 4]);
        FaqItem::factory()->forPlacement(FaqPlacement::Contact)->create(['order' => 9]);

        Livewire::actingAs($this->admin())
            ->test(CreateFaqItem::class)
            ->fillForm([
                'placement' => FaqPlacement::Product->value,
                'question' => 'Apakah ada cicilan?',
                'answer' => 'Ada, hubungi kami.',
                'category' => 'Harusnya dibuang',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = FaqItem::query()->where('question', 'Apakah ada cicilan?')->firstOrFail();

        $this->assertSame(FaqPlacement::Product, $created->placement);
        $this->assertSame(5, $created->order);
        $this->assertNull($created->category);
        $this->assertTrue($created->is_active);
    }

    public function test_faq_page_entries_keep_their_category(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateFaqItem::class)
            ->fillForm([
                'placement' => FaqPlacement::Faq->value,
                'question' => 'Berapa lama pemasangan?',
                'answer' => 'Sekitar 3 hari.',
                'category' => 'Instalasi',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('Instalasi', FaqItem::query()->where('question', 'Berapa lama pemasangan?')->firstOrFail()->category);
    }

    public function test_question_and_answer_are_required(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateFaqItem::class)
            ->fillForm(['placement' => FaqPlacement::Faq->value, 'question' => '', 'answer' => ''])
            ->call('create')
            ->assertHasFormErrors(['question' => 'required', 'answer' => 'required']);
    }

    public function test_admin_can_edit_deactivate_and_delete(): void
    {
        $item = FaqItem::factory()->create(['question' => 'Lama?', 'is_active' => true]);

        Livewire::actingAs($this->admin())
            ->test(EditFaqItem::class, ['record' => $item->getKey()])
            ->fillForm(['question' => 'Baru?', 'is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru?', $item->fresh()->question);
        $this->assertFalse($item->fresh()->is_active);

        Livewire::actingAs($this->admin())
            ->test(ListFaqItems::class)
            ->callTableAction('delete', $item);

        $this->assertModelMissing($item);
    }

    public function test_admin_can_reorder_entries_within_a_placement(): void
    {
        $a = FaqItem::factory()->create(['order' => 0]);
        $b = FaqItem::factory()->create(['order' => 1]);
        $c = FaqItem::factory()->create(['order' => 2]);

        FaqItem::query()->forPlacement(FaqPlacement::Faq)->whereNotIn('id', [$a->id, $b->id, $c->id])->delete();

        Livewire::actingAs($this->admin())
            ->test(ListFaqItems::class)
            ->call('reorderTable', [$c->id, $a->id, $b->id]);

        $this->assertSame(
            [$c->id, $a->id, $b->id],
            FaqItem::query()->forPlacement(FaqPlacement::Faq)->orderBy('order')->pluck('id')->all(),
        );
    }

    public function test_resource_lives_in_the_page_content_group_with_the_faq_label(): void
    {
        $this->assertSame('Konten Halaman', FaqItemResource::getNavigationGroup());
        $this->assertSame('FAQ', FaqItemResource::getNavigationLabel());
    }
}
