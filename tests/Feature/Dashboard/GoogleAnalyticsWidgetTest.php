<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Services\Analytics\AnalyticsAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleAnalyticsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_successfully_when_analytics_credentials_are_missing(): void
    {
        // Panel access without a FilamentUser implementation is only allowed in the local env.
        config([
            'app.env' => 'local',
            'analytics.property_id' => null,
            'analytics.service_account_credentials_json' => storage_path('app/analytics/does-not-exist.json'),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $this->assertFalse(app(AnalyticsAvailability::class)->isConfigured());
    }

    public function test_analytics_availability_reports_configured_when_credentials_are_present(): void
    {
        $credentialsPath = storage_path('app/analytics/service-account-credentials-test.json');
        file_put_contents($credentialsPath, json_encode(['type' => 'service_account']));

        config([
            'analytics.property_id' => '123456789',
            'analytics.service_account_credentials_json' => $credentialsPath,
        ]);

        $this->assertTrue(app(AnalyticsAvailability::class)->isConfigured());

        unlink($credentialsPath);
    }
}
