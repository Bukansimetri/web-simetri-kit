<?php

namespace Tests\Feature\Pages;

use App\Models\Article;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticlePreviewTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'super_admin']));

        return $user;
    }

    public function test_guest_is_redirected_to_the_admin_login(): void
    {
        $draft = Article::factory()->create(['published_at' => null]);

        $this->get(route('artikel.preview', $draft))
            ->assertRedirect(Filament::getPanel('admin')->getLoginUrl());
    }

    public function test_user_without_panel_access_gets_403(): void
    {
        $draft = Article::factory()->create(['published_at' => null]);

        $this->actingAs(User::factory()->create())
            ->get(route('artikel.preview', $draft))
            ->assertForbidden();
    }

    public function test_admin_can_preview_draft_and_scheduled_articles_with_banner_and_noindex(): void
    {
        $admin = $this->adminUser();
        $draft = Article::factory()->create(['title' => 'Draf Rahasia', 'published_at' => null]);
        $scheduled = Article::factory()->create(['title' => 'Terjadwal Rahasia', 'published_at' => now()->addWeek()]);

        foreach ([$draft, $scheduled] as $article) {
            $this->actingAs($admin)->get(route('artikel.preview', $article))
                ->assertOk()
                ->assertSee($article->title)
                ->assertSee('Mode Preview')
                ->assertSee('noindex', escape: false);
        }
    }

    public function test_preview_does_not_increase_view_count_and_normal_page_has_no_banner(): void
    {
        $admin = $this->adminUser();
        $article = Article::factory()->create(['view_count' => 10]);

        $this->actingAs($admin)->get(route('artikel.preview', $article))->assertOk();
        $this->assertSame(10, $article->fresh()->view_count);

        $this->actingAs($admin)->get('/artikel/'.$article->slug)->assertOk()->assertDontSee('Mode Preview');
        $this->assertSame(11, $article->fresh()->view_count);
    }

    public function test_draft_is_still_hidden_on_the_public_url(): void
    {
        $draft = Article::factory()->create(['published_at' => null]);

        $this->actingAs($this->adminUser())->get('/artikel/'.$draft->slug)->assertNotFound();
    }
}
