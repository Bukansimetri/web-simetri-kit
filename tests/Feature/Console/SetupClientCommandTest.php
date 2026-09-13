<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SetupClientCommandTest extends TestCase
{
    private string $envDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->envDir = sys_get_temp_dir().'/setup-client-test-'.uniqid();
        File::ensureDirectoryExists($this->envDir);

        $this->app->useEnvironmentPath($this->envDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->envDir);

        parent::tearDown();
    }

    private function putEnvExample(string $contents = "APP_NAME=Laravel\nAPP_KEY=\n"): void
    {
        File::put($this->envDir.'/.env.example', $contents);
    }

    private function envContents(): string
    {
        return File::get($this->envDir.'/.env');
    }

    private function putEnvWithExistingKey(): void
    {
        File::put($this->envDir.'/.env.example', "APP_NAME=Laravel\nAPP_KEY=\n");
        File::put($this->envDir.'/.env', "APP_NAME=Laravel\nAPP_KEY=base64:existingkey==\n");
    }

    public function test_fresh_install_creates_env_with_app_name_and_key(): void
    {
        $this->putEnvExample();

        $this->artisan('app:setup-client', ['name' => 'PT Sinar Abadi'])
            ->assertExitCode(0);

        $this->assertFileExists($this->envDir.'/.env');
        $this->assertStringContainsString('APP_NAME="PT Sinar Abadi"', $this->envContents());
        $this->assertMatchesRegularExpression('/APP_KEY=base64:.+/', $this->envContents());
    }

    public function test_empty_name_fails_without_creating_env(): void
    {
        $this->putEnvExample();

        $this->artisan('app:setup-client', ['name' => '   '])
            ->assertExitCode(1);

        $this->assertFileDoesNotExist($this->envDir.'/.env');
    }

    public function test_missing_env_example_fails(): void
    {
        $this->artisan('app:setup-client', ['name' => 'Klien'])
            ->assertExitCode(1);

        $this->assertFileDoesNotExist($this->envDir.'/.env');
    }

    public function test_clears_all_caches(): void
    {
        $this->putEnvExample();
        Cache::put('setup_client_test_marker', 'masih-ada', 60);

        $this->artisan('app:setup-client', ['name' => 'Klien'])
            ->expectsOutputToContain('Configuration cache cleared successfully.')
            ->expectsOutputToContain('Route cache cleared successfully.')
            ->expectsOutputToContain('Compiled views cleared successfully.')
            ->expectsOutputToContain('Application cache cleared successfully.')
            ->assertExitCode(0);

        $this->assertFalse(Cache::has('setup_client_test_marker'));
    }

    public function test_caches_are_cleared_even_when_env_and_key_steps_are_skipped(): void
    {
        $this->putEnvWithExistingKey();
        Cache::put('setup_client_test_marker', 'masih-ada', 60);

        $this->artisan('app:setup-client', ['name' => 'Klien'])
            ->expectsOutputToContain('Application cache cleared successfully.')
            ->assertExitCode(0);

        $this->assertFalse(Cache::has('setup_client_test_marker'));
    }

    public function test_rerun_without_force_does_not_change_existing_env_or_key(): void
    {
        $this->putEnvWithExistingKey();
        $before = $this->envContents();

        $this->artisan('app:setup-client', ['name' => 'Nama Baru'])
            ->assertExitCode(0);

        $this->assertSame($before, $this->envContents());
    }

    public function test_rerun_with_force_overwrites_env_and_regenerates_key(): void
    {
        $this->putEnvWithExistingKey();

        $this->artisan('app:setup-client', ['name' => 'Nama Baru', '--force' => true])
            ->assertExitCode(0);

        $this->assertStringContainsString('APP_NAME="Nama Baru"', $this->envContents());
        $this->assertStringNotContainsString('APP_KEY=base64:existingkey==', $this->envContents());
        $this->assertMatchesRegularExpression('/APP_KEY=base64:.+/', $this->envContents());
    }
}
