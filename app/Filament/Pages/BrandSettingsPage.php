<?php

namespace App\Filament\Pages;

use App\Settings\BrandSettings;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\Rule;

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
            'secondary_color' => $settings->secondary_color ?: BrandSettings::DEFAULT_SECONDARY_COLOR,
            'font_heading' => $settings->font_heading ?: BrandSettings::DEFAULT_FONT_HEADING,
            'font_body' => $settings->font_body ?: BrandSettings::DEFAULT_FONT_BODY,
            'og_image_path' => $settings->og_image_path,
            'whatsapp_number' => $settings->whatsapp_number,
            'contact_notification_email' => $settings->contact_notification_email,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identitas Brand')
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
                    ]),
                Section::make('Theme Settings')
                    ->description('Diterapkan ke seluruh halaman publik lewat CSS variable (FR-002). Default mengikuti desain Luminous Azure.')
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label('Warna Primer'),
                        ColorPicker::make('secondary_color')
                            ->label('Warna Sekunder'),
                        Select::make('font_heading')
                            ->label('Font Heading')
                            ->options(array_combine(BrandSettings::FONT_OPTIONS, BrandSettings::FONT_OPTIONS))
                            ->native(false)
                            ->nullable()
                            ->rule(Rule::in(BrandSettings::FONT_OPTIONS)),
                        Select::make('font_body')
                            ->label('Font Body')
                            ->options(array_combine(BrandSettings::FONT_OPTIONS, BrandSettings::FONT_OPTIONS))
                            ->native(false)
                            ->nullable()
                            ->rule(Rule::in(BrandSettings::FONT_OPTIONS)),
                        FileUpload::make('og_image_path')
                            ->label('OG Image (share sosial media)')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->helperText('Dipakai sebagai gambar preview saat link situs dibagikan. Default: gambar bawaan Luminous Azure.'),
                    ]),
                Section::make('Kontak & Notifikasi')
                    ->description('Dipakai oleh form Kontak (AMC-216) — kosongkan bila belum ingin mengaktifkan salah satu.')
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp Bisnis')
                            ->placeholder('6281234567890')
                            ->helperText('Format angka saja (kode negara tanpa +). Dipakai untuk redirect wa.me setelah pengunjung submit form Kontak.')
                            ->tel(),
                        TextInput::make('contact_notification_email')
                            ->label('Email Notifikasi Kontak')
                            ->email()
                            ->helperText('Menerima email setiap ada submission baru dari form Kontak.'),
                    ]),
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
        $settings->secondary_color = $data['secondary_color'] ?? null;
        $settings->font_heading = $data['font_heading'] ?? null;
        $settings->font_body = $data['font_body'] ?? null;
        $settings->og_image_path = $data['og_image_path'] ?? null;
        $settings->whatsapp_number = $data['whatsapp_number'] ?? null;
        $settings->contact_notification_email = $data['contact_notification_email'] ?? null;
        $settings->save();

        Notification::make()
            ->success()
            ->title('Brand settings tersimpan')
            ->send();
    }
}
