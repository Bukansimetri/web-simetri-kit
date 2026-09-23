<?php

namespace App\Filament\Pages;

use App\Settings\AppearanceSettings;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\Rule;

class AppearanceSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Pengaturan Situs';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Tampilan';

    protected static ?string $title = 'Pengaturan Tampilan';

    protected static string $view = 'filament.pages.settings-form-page';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(AppearanceSettings::class);

        $this->form->fill([
            'logo_path' => $settings->logo_path,
            'favicon_path' => $settings->favicon_path,
            'primary_color' => $settings->primary_color,
            'secondary_color' => $settings->secondary_color ?: AppearanceSettings::DEFAULT_SECONDARY_COLOR,
            'font_heading' => $settings->font_heading ?: AppearanceSettings::DEFAULT_FONT_HEADING,
            'font_body' => $settings->font_body ?: AppearanceSettings::DEFAULT_FONT_BODY,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Logo & Favicon')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('branding'),
                        FileUpload::make('favicon_path')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('branding'),
                    ]),
                Section::make('Warna & Font')
                    ->description('Diterapkan ke seluruh halaman publik lewat CSS variable (FR-071). Default mengikuti desain Luminous Azure.')
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label('Warna Primer'),
                        ColorPicker::make('secondary_color')
                            ->label('Warna Sekunder'),
                        Select::make('font_heading')
                            ->label('Font Heading')
                            ->options(array_combine(AppearanceSettings::FONT_OPTIONS, AppearanceSettings::FONT_OPTIONS))
                            ->native(false)
                            ->nullable()
                            ->rule(Rule::in(AppearanceSettings::FONT_OPTIONS)),
                        Select::make('font_body')
                            ->label('Font Body')
                            ->options(array_combine(AppearanceSettings::FONT_OPTIONS, AppearanceSettings::FONT_OPTIONS))
                            ->native(false)
                            ->nullable()
                            ->rule(Rule::in(AppearanceSettings::FONT_OPTIONS)),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = app(AppearanceSettings::class);
        $settings->logo_path = $data['logo_path'] ?? null;
        $settings->favicon_path = $data['favicon_path'] ?? null;
        $settings->primary_color = $data['primary_color'] ?? null;
        $settings->secondary_color = $data['secondary_color'] ?? null;
        $settings->font_heading = $data['font_heading'] ?? null;
        $settings->font_body = $data['font_body'] ?? null;
        $settings->save();

        Notification::make()
            ->success()
            ->title('Pengaturan tampilan tersimpan')
            ->send();
    }
}
