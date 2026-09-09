<?php

namespace Tests\Feature\Pages;

use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AboutPageTeamMembersTest extends TestCase
{
    use RefreshDatabase;

    protected function makeMember(array $attributes = []): TeamMember
    {
        $member = TeamMember::factory()->create($attributes);
        Storage::disk('public')->put($member->photo_path, 'fake-bytes');

        return $member;
    }

    public function test_active_member_is_shown_on_about_page(): void
    {
        Storage::fake('public');
        $this->makeMember([
            'name' => 'Andi Wijaya',
            'position' => 'CEO & Founder',
            'bio' => 'Memimpin visi perusahaan.',
        ]);

        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('Andi Wijaya', escape: false);
        $response->assertSee('CEO &amp; Founder', escape: false);
        $response->assertSee('Memimpin visi perusahaan.', escape: false);
    }

    public function test_inactive_member_is_not_shown(): void
    {
        Storage::fake('public');
        $this->makeMember(['name' => 'Anggota Nonaktif', 'is_active' => false]);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertDontSee('Anggota Nonaktif', escape: false);
    }

    public function test_members_render_in_order(): void
    {
        Storage::fake('public');
        $this->makeMember(['name' => 'Kedua', 'order' => 2]);
        $this->makeMember(['name' => 'Pertama', 'order' => 1]);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertSeeInOrder(['Pertama', 'Kedua'], escape: false);
    }

    public function test_member_with_linkedin_is_a_new_tab_anchor(): void
    {
        Storage::fake('public');
        $this->makeMember(['name' => 'Sari', 'linkedin_url' => 'https://linkedin.com/in/sari']);

        $response = $this->get('/tentang-kami');

        $response->assertSee('href="https://linkedin.com/in/sari"', escape: false);
        $response->assertSee('target="_blank"', escape: false);
    }

    public function test_member_without_linkedin_has_no_anchor_to_it(): void
    {
        Storage::fake('public');
        $this->makeMember(['name' => 'TanpaLinkedIn', 'linkedin_url' => null]);

        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('TanpaLinkedIn', escape: false);
        $response->assertDontSee('linkedin.com/in/', escape: false);
    }

    public function test_about_page_renders_without_team_section_when_none_active(): void
    {
        Storage::fake('public');
        $this->makeMember(['is_active' => false]);

        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('Nilai-Nilai Kami', escape: false);
        $response->assertSee('Visi Kami', escape: false);
        $response->assertDontSee('Tim Kami', escape: false);
    }

    public function test_home_page_does_not_show_team_section(): void
    {
        Storage::fake('public');
        $this->makeMember(['name' => 'Anggota Beranda']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Anggota Beranda', escape: false);
    }
}
