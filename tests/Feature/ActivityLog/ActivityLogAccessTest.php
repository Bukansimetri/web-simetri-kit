<?php

namespace Tests\Feature\ActivityLog;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_user_records_an_activity_log_entry_with_before_and_after_values(): void
    {
        $actor = User::factory()->create();
        $subject = User::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($actor);
        $subject->update(['name' => 'Nama Baru']);

        $activity = Activity::query()->latest('id')->first();

        $this->assertNotNull($activity);
        $this->assertSame($subject->id, $activity->subject_id);
        $this->assertSame($actor->id, $activity->causer_id);
        $this->assertSame('Nama Lama', $activity->properties->get('old')['name']);
        $this->assertSame('Nama Baru', $activity->properties->get('attributes')['name']);
    }

    public function test_super_admin_can_access_activity_log_page(): void
    {
        config(['app.env' => 'local']);
        Role::create(['name' => 'super_admin']);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $response = $this->actingAs($superAdmin)->get('/admin/activitylogs');

        $response->assertOk();
    }

    public function test_non_super_admin_cannot_access_activity_log_page(): void
    {
        config(['app.env' => 'local']);
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'Viewer']);

        $user = User::factory()->create();
        $user->assignRole('Viewer');

        $response = $this->actingAs($user)->get('/admin/activitylogs');

        $response->assertForbidden();
    }
}
