<?php

namespace Tests\Feature\Admin;

use App\Enums\CustomPageTemplate;
use App\Filament\Resources\CustomPageResource\Pages\CreateCustomPage;
use App\Filament\Resources\CustomPageResource\Pages\EditCustomPage;
use App\Filament\Resources\CustomPageResource\Pages\ListCustomPages;
use App\Models\CustomPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class CustomPageLegalTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_new_page_defaults_to_standard_template_with_content_field(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateCustomPage::class)
            ->assertFormSet(['template' => CustomPageTemplate::Standard->value])
            ->assertFormFieldIsVisible('content')
            ->assertFormFieldIsHidden('legal.subtitle');
    }

    public function test_legal_template_shows_legal_fields_and_hides_content(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateCustomPage::class)
            ->fillForm(['template' => CustomPageTemplate::Legal->value])
            ->assertFormFieldIsHidden('content')
            ->assertFormFieldIsVisible('legal.subtitle')
            ->assertFormFieldIsVisible('legal.sections')
            ->assertFormFieldIsVisible('legal.pdf_path')
            ->assertFormFieldIsVisible('legal.cta_title');
    }

    public function test_admin_can_save_a_legal_page_without_content_and_it_renders(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateCustomPage::class)
            ->fillForm([
                'title' => 'Kebijakan Cookie',
                'template' => CustomPageTemplate::Legal->value,
                'legal' => [
                    'subtitle' => 'Penjelasan cookie.',
                    'sections' => [
                        ['title' => 'Apa itu Cookie', 'label' => 'Dasar', 'body' => '<p>Isi cookie.</p>', 'cards' => [['icon' => 'lock', 'title' => 'Aman', 'text' => 'Terlindungi.']]],
                        ['title' => 'Pengaturan', 'body' => '<p>Atur sendiri.</p>'],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = CustomPage::query()->where('slug', 'kebijakan-cookie')->firstOrFail();

        $this->assertSame(CustomPageTemplate::Legal, $page->template);
        $this->assertNull($page->content);
        $this->assertSame('Apa itu Cookie', $page->legal['sections'][0]['title']);
        $this->assertSame('Pengaturan', $page->legal['sections'][1]['title']);
        $this->assertSame('lock', $page->legal['sections'][0]['cards'][0]['icon']);

        $this->get('/halaman/kebijakan-cookie')
            ->assertOk()
            ->assertSeeInOrder(['Apa itu Cookie', 'Pengaturan']);
    }

    public function test_section_title_is_required(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateCustomPage::class)
            ->fillForm([
                'title' => 'Dokumen',
                'template' => CustomPageTemplate::Legal->value,
                'legal' => ['sections' => [['title' => '', 'body' => 'x']]],
            ])
            ->call('create')
            ->assertHasFormErrors(['legal.sections.0.title']);
    }

    public function test_card_icon_outside_curated_list_is_rejected(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateCustomPage::class)
            ->fillForm([
                'title' => 'Dokumen',
                'template' => CustomPageTemplate::Legal->value,
                'legal' => ['sections' => [['title' => 'Bagian', 'cards' => [['icon' => 'tidak_ada_ikon', 'title' => 'Kartu']]]]],
            ])
            ->call('create')
            ->assertHasFormErrors();
    }

    public function test_standard_template_still_requires_content(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateCustomPage::class)
            ->fillForm(['title' => 'Biasa', 'template' => CustomPageTemplate::Standard->value, 'content' => ''])
            ->call('create')
            ->assertHasFormErrors(['content']);
    }

    public function test_switching_templates_keeps_the_other_templates_data(): void
    {
        $page = CustomPage::factory()->legal(['subtitle' => 'Subjudul lama', 'sections' => [['title' => 'Bagian Lama']]])->create(['slug' => 'ganti']);

        Livewire::actingAs($this->admin())
            ->test(EditCustomPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['template' => CustomPageTemplate::Standard->value, 'content' => '<p>Isi biasa.</p>'])
            ->call('save')
            ->assertHasNoFormErrors();

        $page->refresh();
        $this->assertSame(CustomPageTemplate::Standard, $page->template);
        $this->assertSame('Subjudul lama', $page->legal['subtitle']);

        Livewire::actingAs($this->admin())
            ->test(EditCustomPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['template' => CustomPageTemplate::Legal->value])
            ->assertFormSet(['legal.subtitle' => 'Subjudul lama'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(CustomPageTemplate::Legal, $page->fresh()->template);
        $this->assertSame('Bagian Lama', $page->fresh()->legal['sections'][0]['title']);
    }

    public function test_admin_can_edit_an_installed_default_page(): void
    {
        $page = CustomPage::query()->where('slug', 'syarat-ketentuan')->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(EditCustomPage::class, ['record' => $page->getRouteKey()])
            ->assertFormSet(['template' => CustomPageTemplate::Legal->value])
            ->fillForm(['legal.subtitle' => 'Subjudul baru dari admin.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/halaman/syarat-ketentuan')->assertOk()->assertSee('Subjudul baru dari admin.');
    }

    /**
     * @return Testable
     */
    private function createLegalWith(UploadedFile $pdf)
    {
        return Livewire::actingAs($this->admin())
            ->test(CreateCustomPage::class)
            ->fillForm(['title' => 'Dokumen PDF', 'template' => CustomPageTemplate::Legal->value, 'legal' => ['sections' => [], 'pdf_path' => $pdf]])
            ->call('create');
    }

    public function test_pdf_upload_is_stored_and_available_to_the_page(): void
    {
        Storage::fake('public');

        $this->createLegalWith(UploadedFile::fake()->create('salinan.pdf', 200, 'application/pdf'))
            ->assertHasNoFormErrors();

        $page = CustomPage::query()->where('slug', 'dokumen-pdf')->firstOrFail();
        $this->assertStringEndsWith('.pdf', $page->legal['pdf_path']);
        Storage::disk('public')->assertExists($page->legal['pdf_path']);
    }

    public function test_pdf_upload_rejects_non_pdf_files(): void
    {
        Storage::fake('public');

        $this->createLegalWith(UploadedFile::fake()->create('gambar.jpg', 100, 'image/jpeg'))
            ->assertHasFormErrors(['legal.pdf_path']);
    }

    public function test_pdf_upload_rejects_files_over_ten_megabytes(): void
    {
        Storage::fake('public');

        $this->createLegalWith(UploadedFile::fake()->create('besar.pdf', 11000, 'application/pdf'))
            ->assertHasFormErrors(['legal.pdf_path']);
    }

    public function test_table_shows_the_template_column(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListCustomPages::class)
            ->assertCanRenderTableColumn('template')
            ->assertSee('Dokumen Legal');
    }

    public function test_existing_standard_page_can_still_be_edited(): void
    {
        $page = CustomPage::factory()->create(['slug' => 'lama', 'title' => 'Lama']);

        Livewire::actingAs($this->admin())
            ->test(EditCustomPage::class, ['record' => $page->getRouteKey()])
            ->assertFormSet(['template' => CustomPageTemplate::Standard->value])
            ->fillForm(['title' => 'Lama Diubah'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Lama Diubah', $page->fresh()->title);
    }
}
