<?php

namespace Tests\Feature\Docs;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Menjaga manual operator (AMC-234) tetap sesuai panel: setiap label menu
 * yang benar-benar dirender panel harus disebut di manual (FR-004, FR-015,
 * SC-003), dan manual tetap bebas kode/path/nama vendor (FR-002, FR-014,
 * SC-005).
 */
class OperatorManualTest extends TestCase
{
    use RefreshDatabase;

    private const MANUAL = 'docs/manual-operator.md';

    public function test_every_panel_menu_is_documented(): void
    {
        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        Gate::before(fn () => true);

        $this->actingAs($user);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();

        $labels = [];

        foreach (Filament::getNavigation() as $group) {
            if (filled($group->getLabel())) {
                $labels[] = $group->getLabel();
            }

            foreach ($group->getItems() as $item) {
                $labels[] = $item->getLabel();
            }
        }

        $this->assertNotEmpty($labels, 'Navigasi panel kosong — cek pengaturan test.');

        $this->assertFileExists(base_path(self::MANUAL), self::MANUAL.' belum dibuat.');
        $manual = file_get_contents(base_path(self::MANUAL));

        $missing = [];

        foreach (array_unique($labels) as $label) {
            if (! str_contains($manual, "**{$label}**")) {
                $missing[] = $label;
            }
        }

        $this->assertSame([], $missing, 'Label menu panel belum disebut di manual: '.implode(', ', $missing));
    }

    public function test_manual_has_no_code_paths_or_terminal_commands(): void
    {
        $this->assertFileExists(base_path(self::MANUAL), self::MANUAL.' belum dibuat.');
        $manual = file_get_contents(base_path(self::MANUAL));

        $this->assertStringNotContainsString('`', $manual, 'Manual tidak boleh memuat backtick (kode/path).');

        $this->assertDoesNotMatchRegularExpression(
            '/\b(php artisan|composer (install|require|update)|npm (run|install)|git (pull|push|clone))\b/i',
            $manual,
            'Manual tidak boleh memuat perintah terminal.'
        );
    }

    public function test_manual_is_client_and_vendor_neutral(): void
    {
        $this->assertFileExists(base_path(self::MANUAL), self::MANUAL.' belum dibuat.');
        $manual = file_get_contents(base_path(self::MANUAL));

        foreach (['Simetri', 'Solarpanel', 'Filament', 'Laravel'] as $term) {
            $this->assertStringNotContainsStringIgnoringCase($term, $manual, "Manual tidak boleh menyebut \"{$term}\".");
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function requiredSections(): array
    {
        return [
            'US1 akun' => ['Masuk, keluar, dan akun Anda'],
            'US1 peta menu' => ['Mengenal panel'],
            'US1 konten' => ['Mengelola konten'],
            'US1 jeda tampil' => ['Kapan perubahan tampil di situs'],
            'US2 prospek' => ['Dasbor dan prospek'],
            'US3 pengaturan' => ['Pengaturan situs'],
            'US4 pengguna' => ['Pengguna dan peran'],
            'polish masalah umum' => ['Masalah umum'],
            'polish istilah' => ['Istilah'],
        ];
    }

    #[DataProvider('requiredSections')]
    public function test_required_section_exists(string $heading): void
    {
        $this->assertFileExists(base_path(self::MANUAL), self::MANUAL.' belum dibuat.');
        $manual = file_get_contents(base_path(self::MANUAL));

        $this->assertMatchesRegularExpression(
            '/^## '.preg_quote($heading, '/').'$/m',
            $manual,
            "Manual belum memuat bagian \"## {$heading}\"."
        );
    }

    public function test_manual_is_linked_from_readme_and_architecture_doc(): void
    {
        $readme = file_get_contents(base_path('README.md'));
        $this->assertStringContainsString('](docs/manual-operator.md)', $readme, 'README.md belum menautkan manual operator.');

        $arsitektur = file_get_contents(base_path('docs/arsitektur.md'));
        $this->assertStringContainsString('](manual-operator.md)', $arsitektur, 'docs/arsitektur.md belum menautkan manual operator.');
    }
}
