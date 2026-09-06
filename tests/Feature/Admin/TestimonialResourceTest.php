<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\TestimonialResource\Pages\CreateTestimonial;
use App\Filament\Resources\TestimonialResource\Pages\EditTestimonial;
use App\Filament\Resources\TestimonialResource\Pages\ListTestimonials;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TestimonialResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_list_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ListTestimonials::class)
            ->assertOk();
    }

    public function test_admin_can_create_testimonial_without_photo(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateTestimonial::class)
            ->fillForm([
                'name' => 'Budi Santoso',
                'attribution' => 'Pemilik Rumah, Bandung',
                'content' => 'Pemasangan rapi dan hemat listrik terasa.',
                'rating' => 5,
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('testimonials', [
            'name' => 'Budi Santoso',
            'rating' => 5,
            'photo_path' => null,
            'is_active' => true,
        ]);
    }

    public function test_blank_attribution_is_accepted(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateTestimonial::class)
            ->fillForm([
                'name' => 'Sari Dewi',
                'attribution' => null,
                'content' => 'Sangat puas.',
                'rating' => 4,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('testimonials', ['name' => 'Sari Dewi', 'attribution' => null]);
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateTestimonial::class)
            ->fillForm([
                'name' => '',
                'content' => '',
                'rating' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'content', 'rating']);
    }

    public function test_rating_outside_1_to_5_is_rejected(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateTestimonial::class)
            ->fillForm([
                'name' => 'Test',
                'content' => 'Test',
                'rating' => 6,
            ])
            ->call('create')
            ->assertHasFormErrors(['rating']);
    }

    public function test_uploaded_photo_is_converted_to_webp(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateTestimonial::class)
            ->fillForm([
                'name' => 'Rina',
                'content' => 'Mantap.',
                'rating' => 5,
                'photo_path' => UploadedFile::fake()->image('rina.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $testimonial = Testimonial::where('name', 'Rina')->first();

        $this->assertNotNull($testimonial->photo_path);
        $this->assertSame('webp', pathinfo($testimonial->photo_path, PATHINFO_EXTENSION));
        Storage::disk('public')->assertExists($testimonial->photo_path);
    }

    public function test_admin_can_edit_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create(['content' => 'Lama', 'order' => 5]);

        Livewire::actingAs(User::factory()->create())
            ->test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
            ->fillForm(['content' => 'Baru', 'order' => 2])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru', $testimonial->fresh()->content);
        $this->assertSame(2, $testimonial->fresh()->order);
    }

    public function test_admin_can_delete_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ListTestimonials::class)
            ->callTableAction('delete', $testimonial);

        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }
}
