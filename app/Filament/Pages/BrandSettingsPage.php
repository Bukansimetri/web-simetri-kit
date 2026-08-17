<?php

namespace App\Filament\Pages;

use App\Settings\BrandSettings;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BrandSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Brand Settings';

    protected static ?string $title = 'Brand Settings';

    protected static string $view = 'filament.pages.brand-settings-page';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(BrandSettings::class);

        $this->form->fill([
            'app_name' => $settings->app_name,
            'logo_path' => $settings->logo_path,
            'favicon_path' => $settings->favicon_path,
            'primary_color' => $settings->primary_color,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('app_name')
                    ->label('Nama Aplikasi')
                    ->placeholder(config('app.name'))
                    ->maxLength(255),
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
                ColorPicker::make('primary_color')
                    ->label('Warna Primer'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = app(BrandSettings::class);
        $settings->app_name = $data['app_name'] ?? null;
        $settings->logo_path = $data['logo_path'] ?? null;
        $settings->favicon_path = $data['favicon_path'] ?? null;
        $settings->primary_color = $data['primary_color'] ?? null;
        $settings->save();

        Notification::make()
            ->success()
            ->title('Brand settings tersimpan')
            ->send();
    }
}
