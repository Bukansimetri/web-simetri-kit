<?php

namespace App\Services\Analytics;

class AnalyticsAvailability
{
    public function isConfigured(): bool
    {
        if (blank(config('analytics.property_id'))) {
            return false;
        }

        $credentials = config('analytics.service_account_credentials_json');

        if (is_array($credentials)) {
            return $credentials !== [];
        }

        return filled($credentials) && file_exists($credentials);
    }
}
