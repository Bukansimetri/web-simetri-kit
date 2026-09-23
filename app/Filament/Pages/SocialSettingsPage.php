<?php

namespace App\Filament\Pages;

use App\Settings\SocialSettings;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SocialSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Media Sosial';

    protected static ?string $title = 'Pengaturan Media Sosial';

    protected static string $view = 'filament.pages.settings-form-page';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(SocialSettings::class);

        $this->form->fill([
            'facebook_url' => $settings->facebook_url,
            'twitter_url' => $settings->twitter_url,
            'instagram_url' => $settings->instagram_url,
            'linkedin_url' => $settings->linkedin_url,
            'youtube_url' => $settings->youtube_url,
            'pinterest_url' => $settings->pinterest_url,
            'tiktok_url' => $settings->tiktok_url,
            'share_buttons_enabled' => $settings->share_buttons_enabled,
            'share_platforms' => $settings->share_platforms,
            'default_share_image_path' => $settings->default_share_image_path,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Profil Media Sosial')
                    ->description('Tautan ikon media sosial di header halaman publik. Ikon platform yang dikosongkan tidak akan ditampilkan (FR-019, FR-020).')
                    ->schema([
                        TextInput::make('facebook_url')->label('Facebook')->url()->maxLength(255),
                        TextInput::make('twitter_url')->label('Twitter/X')->url()->maxLength(255),
                        TextInput::make('instagram_url')->label('Instagram')->url()->maxLength(255),
                        TextInput::make('linkedin_url')->label('LinkedIn')->url()->maxLength(255),
                        TextInput::make('youtube_url')->label('YouTube')->url()->maxLength(255),
                        TextInput::make('pinterest_url')->label('Pinterest')->url()->maxLength(255),
                        TextInput::make('tiktok_url')->label('TikTok')->url()->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Tombol Berbagi')
                    ->description('Tombol berbagi konten pada halaman artikel dan produk (FR-022, FR-023).')
                    ->schema([
                        Toggle::make('share_buttons_enabled')
                            ->label('Aktifkan Tombol Berbagi')
                            ->live(),
                        CheckboxList::make('share_platforms')
                            ->label('Platform yang Ditampilkan')
                            ->options(SocialSettings::SHARE_PLATFORMS)
                            ->columns(2)
                            ->visible(fn ($get) => $get('share_buttons_enabled')),
                    ]),
                Section::make('Gambar Berbagi Default')
                    ->description('Dipakai saat konten yang dibagikan belum punya gambar SEO sendiri (FR-024).')
                    ->schema([
                        FileUpload::make('default_share_image_path')
                            ->label('Gambar Berbagi Default')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->helperText('Ukuran disarankan 1200×630 piksel.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = app(SocialSettings::class);
        $settings->facebook_url = $data['facebook_url'] ?? null;
        $settings->twitter_url = $data['twitter_url'] ?? null;
        $settings->instagram_url = $data['instagram_url'] ?? null;
        $settings->linkedin_url = $data['linkedin_url'] ?? null;
        $settings->youtube_url = $data['youtube_url'] ?? null;
        $settings->pinterest_url = $data['pinterest_url'] ?? null;
        $settings->tiktok_url = $data['tiktok_url'] ?? null;
        $settings->share_buttons_enabled = $data['share_buttons_enabled'] ?? false;
        $settings->share_platforms = $data['share_platforms'] ?? [];
        $settings->default_share_image_path = $data['default_share_image_path'] ?? null;
        $settings->save();

        Notification::make()
            ->success()
            ->title('Pengaturan media sosial tersimpan')
            ->send();
    }
}
