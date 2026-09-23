<?php

namespace App\Filament\Pages;

use App\Settings\ScriptSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

/**
 * Halaman kode mentah yang tersuntik apa adanya ke halaman publik (FR-044).
 * Hanya `super_admin` yang boleh melihat maupun menyimpannya (FR-045) — pola
 * pembatasan ini mengikuti ActivitylogPlugin di AdminPanelProvider.
 */
class ScriptSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationGroup = 'Pengaturan Situs';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Scripts & Analytics';

    protected static ?string $title = 'Scripts & Analytics';

    protected static string $view = 'filament.pages.settings-form-page';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $settings = app(ScriptSettings::class);

        $this->form->fill([
            'head_scripts' => $settings->head_scripts,
            'body_start_scripts' => $settings->body_start_scripts,
            'body_end_scripts' => $settings->body_end_scripts,
            'footer_scripts' => $settings->footer_scripts,
            'custom_css' => $settings->custom_css,
            'custom_js' => $settings->custom_js,
            'head_scripts_consent' => $settings->head_scripts_consent,
            'body_start_scripts_consent' => $settings->body_start_scripts_consent,
            'body_end_scripts_consent' => $settings->body_end_scripts_consent,
            'footer_scripts_consent' => $settings->footer_scripts_consent,
            'custom_css_consent' => $settings->custom_css_consent,
            'custom_js_consent' => $settings->custom_js_consent,
            'cookie_consent_enabled' => $settings->cookie_consent_enabled,
            'cookie_banner_message' => $settings->cookie_banner_message,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Scripts')
                    ->tabs([
                        Tab::make('Scripts')
                            ->schema([
                                Section::make('Slot Kode')
                                    ->description('Dimuat apa adanya sebagai kode pada halaman publik — tidak pernah di panel admin (FR-041, FR-044, FR-046).')
                                    ->schema([
                                        Textarea::make('head_scripts')
                                            ->label('Head Scripts')
                                            ->helperText('Dimuat di dalam <head>.')
                                            ->rows(5)
                                            ->extraInputAttributes(['class' => 'font-mono text-sm']),
                                        Select::make('head_scripts_consent')
                                            ->label('Kategori Persetujuan')
                                            ->options(ScriptSettings::CONSENT_CATEGORIES)
                                            ->native(false),
                                        Textarea::make('body_start_scripts')
                                            ->label('Body Start Scripts')
                                            ->helperText('Dimuat tepat setelah <body> dibuka.')
                                            ->rows(5)
                                            ->extraInputAttributes(['class' => 'font-mono text-sm']),
                                        Select::make('body_start_scripts_consent')
                                            ->label('Kategori Persetujuan')
                                            ->options(ScriptSettings::CONSENT_CATEGORIES)
                                            ->native(false),
                                        Textarea::make('body_end_scripts')
                                            ->label('Body End Scripts')
                                            ->helperText('Dimuat tepat sebelum </body>.')
                                            ->rows(5)
                                            ->extraInputAttributes(['class' => 'font-mono text-sm']),
                                        Select::make('body_end_scripts_consent')
                                            ->label('Kategori Persetujuan')
                                            ->options(ScriptSettings::CONSENT_CATEGORIES)
                                            ->native(false),
                                        Textarea::make('footer_scripts')
                                            ->label('Footer Scripts')
                                            ->helperText('Dimuat pada bagian footer.')
                                            ->rows(5)
                                            ->extraInputAttributes(['class' => 'font-mono text-sm']),
                                        Select::make('footer_scripts_consent')
                                            ->label('Kategori Persetujuan')
                                            ->options(ScriptSettings::CONSENT_CATEGORIES)
                                            ->native(false),
                                    ]),
                            ]),
                        Tab::make('Custom Code')
                            ->schema([
                                Section::make('CSS dan JavaScript Kustom')
                                    ->schema([
                                        Textarea::make('custom_css')
                                            ->label('Custom CSS')
                                            ->helperText('Dimuat sebagai gaya di <head>.')
                                            ->rows(6)
                                            ->extraInputAttributes(['class' => 'font-mono text-sm']),
                                        Select::make('custom_css_consent')
                                            ->label('Kategori Persetujuan')
                                            ->options(ScriptSettings::CONSENT_CATEGORIES)
                                            ->native(false),
                                        Textarea::make('custom_js')
                                            ->label('Custom JavaScript')
                                            ->helperText('Dimuat sebelum </body>.')
                                            ->rows(6)
                                            ->extraInputAttributes(['class' => 'font-mono text-sm']),
                                        Select::make('custom_js_consent')
                                            ->label('Kategori Persetujuan')
                                            ->options(ScriptSettings::CONSENT_CATEGORIES)
                                            ->native(false),
                                    ]),
                            ]),
                        Tab::make('Cookie Consent')
                            ->schema([
                                Section::make('Persetujuan Cookie')
                                    ->description('Persetujuan pengunjung diingat pada perangkatnya sendiri, tidak dicatat di sisi sistem (FR-058).')
                                    ->schema([
                                        Toggle::make('cookie_consent_enabled')
                                            ->label('Aktifkan Pemberitahuan Persetujuan Cookie')
                                            ->live(),
                                        Textarea::make('cookie_banner_message')
                                            ->label('Pesan Bilah Persetujuan')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->visible(fn ($get) => $get('cookie_consent_enabled')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();

        $settings = app(ScriptSettings::class);
        $this->assertWithinLimit($data['head_scripts'] ?? null);
        $this->assertWithinLimit($data['body_start_scripts'] ?? null);
        $this->assertWithinLimit($data['body_end_scripts'] ?? null);
        $this->assertWithinLimit($data['footer_scripts'] ?? null);
        $this->assertWithinLimit($data['custom_css'] ?? null);
        $this->assertWithinLimit($data['custom_js'] ?? null);

        $settings->head_scripts = $data['head_scripts'] ?? null;
        $settings->body_start_scripts = $data['body_start_scripts'] ?? null;
        $settings->body_end_scripts = $data['body_end_scripts'] ?? null;
        $settings->footer_scripts = $data['footer_scripts'] ?? null;
        $settings->custom_css = $data['custom_css'] ?? null;
        $settings->custom_js = $data['custom_js'] ?? null;
        $settings->head_scripts_consent = $data['head_scripts_consent'] ?? ScriptSettings::CONSENT_NONE;
        $settings->body_start_scripts_consent = $data['body_start_scripts_consent'] ?? ScriptSettings::CONSENT_NONE;
        $settings->body_end_scripts_consent = $data['body_end_scripts_consent'] ?? ScriptSettings::CONSENT_NONE;
        $settings->footer_scripts_consent = $data['footer_scripts_consent'] ?? ScriptSettings::CONSENT_NONE;
        $settings->custom_css_consent = $data['custom_css_consent'] ?? ScriptSettings::CONSENT_NONE;
        $settings->custom_js_consent = $data['custom_js_consent'] ?? ScriptSettings::CONSENT_NONE;
        $settings->cookie_consent_enabled = $data['cookie_consent_enabled'] ?? false;
        $settings->cookie_banner_message = $data['cookie_banner_message'] ?? null;
        $settings->save();

        Notification::make()
            ->success()
            ->title('Pengaturan scripts & analytics tersimpan')
            ->send();
    }

    /**
     * Batas ukuran per slot (FR-048) — mencegah admin menyimpan kode yang
     * jauh melampaui kebutuhan pemasangan alat pihak ketiga wajar.
     */
    private function assertWithinLimit(?string $value): void
    {
        if ($value !== null && mb_strlen($value) > ScriptSettings::MAX_SLOT_LENGTH) {
            Notification::make()
                ->danger()
                ->title('Slot melebihi batas ukuran')
                ->body('Maksimal '.number_format(ScriptSettings::MAX_SLOT_LENGTH).' karakter per slot.')
                ->send();

            throw new Halt;
        }
    }
}
