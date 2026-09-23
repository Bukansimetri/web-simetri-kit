<?php

namespace App\Filament\Pages;

use App\Settings\SiteSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\Rule;

class SiteSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Pengaturan Umum';

    protected static ?string $title = 'Pengaturan Umum Situs';

    protected static string $view = 'filament.pages.settings-form-page';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(SiteSettings::class);

        $this->form->fill([
            'site_name' => $settings->site_name,
            'tagline' => $settings->tagline,
            'site_description' => $settings->site_description,
            'company_name' => $settings->company_name,
            'company_email' => $settings->company_email,
            'company_phone' => $settings->company_phone,
            'company_address' => $settings->company_address,
            'default_language' => $settings->default_language,
            'timezone' => $settings->timezone,
            'copyright_text' => $settings->copyright_text,
            'terms_url' => $settings->terms_url,
            'privacy_url' => $settings->privacy_url,
            'cookie_policy_url' => $settings->cookie_policy_url,
            'error_404_message' => $settings->error_404_message,
            'error_500_message' => $settings->error_500_message,
            'maintenance_mode' => $settings->maintenance_mode,
            'whatsapp_number' => $settings->whatsapp_number,
            'contact_notification_email' => $settings->contact_notification_email,
            'career_module_enabled' => $settings->career_module_enabled,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identitas Situs')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Nama Situs')
                            ->placeholder(config('app.name'))
                            ->maxLength(255),
                        TextInput::make('tagline')
                            ->label('Tagline')
                            ->maxLength(255),
                        Textarea::make('site_description')
                            ->label('Deskripsi Situs')
                            ->rows(2)
                            ->maxLength(500),
                    ]),
                Section::make('Informasi Perusahaan')
                    ->description('Ditampilkan di blok kontak footer seluruh halaman publik (FR-002, FR-003).')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Nama Perusahaan')
                            ->maxLength(255),
                        TextInput::make('company_email')
                            ->label('Email Perusahaan')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('company_phone')
                            ->label('Telepon Perusahaan')
                            ->tel()
                            ->maxLength(50),
                        Textarea::make('company_address')
                            ->label('Alamat Perusahaan')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->columns(2),
                Section::make('Pengaturan Regional')
                    ->schema([
                        Select::make('default_language')
                            ->label('Bahasa Default')
                            ->options(SiteSettings::LANGUAGE_OPTIONS)
                            ->native(false)
                            ->required()
                            ->rule(Rule::in(array_keys(SiteSettings::LANGUAGE_OPTIONS))),
                        Select::make('timezone')
                            ->label('Zona Waktu')
                            ->options(SiteSettings::TIMEZONE_OPTIONS)
                            ->native(false)
                            ->required()
                            ->rule(Rule::in(array_keys(SiteSettings::TIMEZONE_OPTIONS))),
                    ])
                    ->columns(2),
                Section::make('Informasi Legal')
                    ->description('Ditampilkan sebagai teks hak cipta dan tautan legal di footer (FR-006, FR-007).')
                    ->schema([
                        TextInput::make('copyright_text')
                            ->label('Teks Hak Cipta')
                            ->maxLength(255)
                            ->helperText('Kosongkan untuk memakai teks bawaan.'),
                        TextInput::make('terms_url')
                            ->label('URL Syarat & Ketentuan')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('privacy_url')
                            ->label('URL Kebijakan Privasi')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('cookie_policy_url')
                            ->label('URL Kebijakan Cookie')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Pesan Error')
                    ->description('Ditampilkan pada halaman kesalahan (FR-015, FR-016).')
                    ->schema([
                        Textarea::make('error_404_message')
                            ->label('Pesan Halaman Tidak Ditemukan (404)')
                            ->rows(2)
                            ->maxLength(500),
                        Textarea::make('error_500_message')
                            ->label('Pesan Gangguan Sistem (500)')
                            ->rows(2)
                            ->maxLength(500),
                    ]),
                Section::make('Mode Pemeliharaan')
                    ->description('Saat aktif, pengunjung halaman publik melihat halaman pemeliharaan; panel admin tetap dapat diakses (FR-010 sampai FR-014).')
                    ->schema([
                        Toggle::make('maintenance_mode')
                            ->label('Aktifkan Mode Pemeliharaan'),
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
                Section::make('Modul Opsional')
                    ->description('Aktifkan/nonaktifkan modul yang tidak dibutuhkan semua klien.')
                    ->schema([
                        Toggle::make('career_module_enabled')
                            ->label('Modul Karir Aktif')
                            ->helperText('Saat dimatikan, halaman /karir tidak lagi bisa diakses dan link "Karir" hilang dari navigasi footer. Data lowongan kerja yang sudah tersimpan TIDAK terhapus.')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = app(SiteSettings::class);
        $settings->site_name = $data['site_name'] ?? null;
        $settings->tagline = $data['tagline'] ?? null;
        $settings->site_description = $data['site_description'] ?? null;
        $settings->company_name = $data['company_name'] ?? null;
        $settings->company_email = $data['company_email'] ?? null;
        $settings->company_phone = $data['company_phone'] ?? null;
        $settings->company_address = $data['company_address'] ?? null;
        $settings->default_language = $data['default_language'];
        $settings->timezone = $data['timezone'];
        $settings->copyright_text = $data['copyright_text'] ?? null;
        $settings->terms_url = $data['terms_url'] ?? null;
        $settings->privacy_url = $data['privacy_url'] ?? null;
        $settings->cookie_policy_url = $data['cookie_policy_url'] ?? null;
        $settings->error_404_message = $data['error_404_message'] ?? null;
        $settings->error_500_message = $data['error_500_message'] ?? null;
        $settings->maintenance_mode = $data['maintenance_mode'] ?? false;
        $settings->whatsapp_number = $data['whatsapp_number'] ?? null;
        $settings->contact_notification_email = $data['contact_notification_email'] ?? null;
        $settings->career_module_enabled = $data['career_module_enabled'] ?? true;
        $settings->save();

        Notification::make()
            ->success()
            ->title('Pengaturan umum tersimpan')
            ->send();
    }
}
