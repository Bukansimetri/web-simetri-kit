<?php

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Membuktikan migration T009 (research.md R2) memindahkan nilai `brand.*`
 * yang sudah tersimpan pada instalasi lama ke grup barunya tanpa kehilangan
 * isi (FR-068), dan grup `brand` tidak lagi menyimpan properti apa pun
 * setelah migrasi (FR-063).
 */
class BrandSettingsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_brand_value_moves_intact_to_its_new_group(): void
    {
        // Simulasikan instalasi lama yang sudah punya nilai admin sungguhan
        // sebelum migration pemindahan berjalan ulang.
        DB::table('settings')->where('group', 'site')->where('name', 'site_name')->delete();
        DB::table('settings')->insert([
            'group' => 'brand',
            'name' => 'app_name',
            'locked' => false,
            'payload' => json_encode('Living Solar Energy'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runMoveMigration();

        $this->assertDatabaseHas('settings', [
            'group' => 'site',
            'name' => 'site_name',
            'payload' => json_encode('Living Solar Energy'),
        ]);
        $this->assertDatabaseMissing('settings', [
            'group' => 'brand',
            'name' => 'app_name',
        ]);
    }

    public function test_brand_group_has_no_properties_left_after_migration(): void
    {
        $this->assertSame(0, DB::table('settings')->where('group', 'brand')->count());
    }

    public function test_move_migration_is_idempotent_when_rerun_on_already_migrated_database(): void
    {
        $this->runMoveMigration();
        $this->runMoveMigration();

        $this->assertSame(
            1,
            DB::table('settings')->where('group', 'site')->where('name', 'site_name')->count()
        );
    }

    private function runMoveMigration(): void
    {
        $migration = require database_path('settings/2026_09_23_100004_move_brand_settings_to_new_groups.php');
        $migration->up();
    }
}
