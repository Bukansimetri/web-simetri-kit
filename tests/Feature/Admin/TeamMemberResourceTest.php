<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\TeamMemberResource\Pages\CreateTeamMember;
use App\Filament\Resources\TeamMemberResource\Pages\EditTeamMember;
use App\Filament\Resources\TeamMemberResource\Pages\ListTeamMembers;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TeamMemberResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_list_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ListTeamMembers::class)
            ->assertOk();
    }

    public function test_admin_can_create_member_without_linkedin(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateTeamMember::class)
            ->fillForm([
                'name' => 'Andi Wijaya',
                'position' => 'CEO & Founder',
                'photo_path' => UploadedFile::fake()->image('andi.jpg', 800, 800),
                'bio' => 'Memimpin visi perusahaan sejak 2015.',
                'order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $member = TeamMember::where('name', 'Andi Wijaya')->first();

        $this->assertNotNull($member);
        $this->assertNull($member->linkedin_url);
        $this->assertSame('webp', pathinfo($member->photo_path, PATHINFO_EXTENSION));
        Storage::disk('public')->assertExists($member->photo_path);
    }

    public function test_wide_photo_is_downscaled_to_800(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateTeamMember::class)
            ->fillForm([
                'name' => 'Foto Lebar',
                'position' => 'Staff',
                'photo_path' => UploadedFile::fake()->image('wide.jpg', 2000, 1000),
                'bio' => 'x',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $path = TeamMember::where('name', 'Foto Lebar')->first()->photo_path;
        $info = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame(800, $info[0]);
        $this->assertSame(400, $info[1]);
    }

    public function test_valid_linkedin_url_is_accepted(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateTeamMember::class)
            ->fillForm([
                'name' => 'Sari',
                'position' => 'CTO',
                'photo_path' => UploadedFile::fake()->image('s.jpg', 400, 400),
                'bio' => 'x',
                'linkedin_url' => 'https://linkedin.com/in/sari',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('team_members', [
            'name' => 'Sari',
            'linkedin_url' => 'https://linkedin.com/in/sari',
        ]);
    }

    public function test_linkedin_url_without_scheme_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateTeamMember::class)
            ->fillForm([
                'name' => 'Sari',
                'position' => 'CTO',
                'photo_path' => UploadedFile::fake()->image('s.jpg', 400, 400),
                'bio' => 'x',
                'linkedin_url' => 'linkedin.com/in/sari',
            ])
            ->call('create')
            ->assertHasFormErrors(['linkedin_url']);
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateTeamMember::class)
            ->fillForm([
                'name' => '',
                'position' => '',
                'photo_path' => null,
                'bio' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'position', 'photo_path', 'bio']);
    }

    public function test_admin_can_edit_member(): void
    {
        Storage::fake('public');
        $member = TeamMember::factory()->create(['position' => 'Lama', 'order' => 5]);
        Storage::disk('public')->put($member->photo_path, 'fake-bytes');

        Livewire::actingAs(User::factory()->create())
            ->test(EditTeamMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['position' => 'Baru', 'order' => 2])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru', $member->fresh()->position);
        $this->assertSame(2, $member->fresh()->order);
    }

    public function test_admin_can_delete_member(): void
    {
        $member = TeamMember::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ListTeamMembers::class)
            ->callTableAction('delete', $member);

        $this->assertDatabaseMissing('team_members', ['id' => $member->id]);
    }
}
