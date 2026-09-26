<?php

namespace Tests\Feature\Dashboard;

use App\Filament\Widgets\LeadsNeedingFollowUp;
use App\Models\CalculatorLead;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadsNeedingFollowUpTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_widget_shows_title_columns_and_rows(): void
    {
        $user = $this->actingUser();

        ContactSubmission::factory()->create([
            'name' => 'Budi Santoso',
            'area' => null,
            'status' => ContactSubmission::STATUS_NEW,
            'created_at' => now()->subHours(49),
        ]);

        CalculatorLead::factory()->create([
            'name' => 'Siti Aminah',
            'status' => CalculatorLead::STATUS_NEW,
            'estimated_monthly_bill' => 1_250_000,
            'created_at' => now()->subHours(2),
        ]);

        Livewire::actingAs($user)
            ->test(LeadsNeedingFollowUp::class)
            ->assertSee('Perlu ditindaklanjuti')
            ->assertSee('Nama')
            ->assertSee('Sumber')
            ->assertSee('Area')
            ->assertSee('Masuk')
            ->assertSee('Estimasi Tagihan')
            ->assertSee('Budi Santoso')
            ->assertSee('Terlambat')
            ->assertSee('Siti Aminah')
            ->assertSee('Rp 1.250.000')
            ->assertSee('-')
            ->assertSee('Lihat semua pesan masuk baru')
            ->assertSee('Lihat semua lead kalkulator baru');
    }

    public function test_widget_hides_non_new_leads_and_shows_empty_state(): void
    {
        $user = $this->actingUser();

        ContactSubmission::factory()->create(['status' => ContactSubmission::STATUS_CONTACTED]);
        CalculatorLead::factory()->won();

        Livewire::actingAs($user)
            ->test(LeadsNeedingFollowUp::class)
            ->assertSee('Semua prospek sudah ditindaklanjuti');
    }
}
