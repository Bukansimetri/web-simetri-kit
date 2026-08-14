<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Editor and Viewer are seeded without permissions since no content
     * resources exist yet; assign permissions via the Filament Shield Role
     * resource as content modules are added.
     */
    public function run(): void
    {
        // Shield grants this role full access via Gate::before, see config/filament-shield.php.
        Role::findOrCreate('super_admin', 'web');

        Role::findOrCreate('Editor', 'web');
        Role::findOrCreate('Viewer', 'web');
    }
}
