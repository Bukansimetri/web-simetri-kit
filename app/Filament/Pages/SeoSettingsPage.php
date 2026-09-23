<?php

namespace App\Filament\Pages;

use App\Settings\SeoSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SeoSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected const CHANGEFREQ_OPTIONS = [
        'always' => 'Selalu',
        'hourly' => 'Tiap Jam',
        'daily' => 'Tiap Hari',
        'weekly' => 'Tiap Minggu',
        'monthly' => 'Tiap Bulan',
        'yearly' => 'Tiap Tahun',
        'never' => 'Tidak Pernah',
    ];

    protected const PRIORITY_OPTIONS = [
        '1.0' => '1.0', '0.9' => '0.9', '0.8' => '0.8', '0.7' => '0.7',
        '0.6' => '0.6', '0.5' => '0.5', '0.4' => '0.4', '0.3' => '0.3',
        '0.2' => '0.2', '0.1' => '0.1', '0.0' => '0.0',
    ];

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'SEO';

    protected static ?string $title = 'Pengaturan SEO';

    protected static string $view = 'filament.pages.settings-form-page';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(SeoSettings::class);

        $this->form->fill([
            'title_separator' => $settings->title_separator,
            'default_title_format' => $settings->default_title_format,
            'page_title_formats' => $settings->page_title_formats,
            'default_meta_description' => $settings->default_meta_description,
            'meta_keywords' => $settings->meta_keywords,
            'default_canonical_url' => $settings->default_canonical_url,
            'allow_indexing' => $settings->allow_indexing,
            'allow_following' => $settings->allow_following,
            'twitter_handle' => $settings->twitter_handle,
            'additional_head_meta' => $settings->additional_head_meta,
            'verification_google' => $settings->verification_google,
            'verification_bing' => $settings->verification_bing,
            'verification_yandex' => $settings->verification_yandex,
            'verification_baidu' => $settings->verification_baidu,
            'robots_txt_content' => $settings->robots_txt_content,
            'sitemap_enabled' => $settings->sitemap_enabled,
            'sitemap_include_pages' => $settings->sitemap_include_pages,
            'sitemap_include_articles' => $settings->sitemap_include_articles,
            'sitemap_include_products' => $settings->sitemap_include_products,
            'sitemap_include_portfolio' => $settings->sitemap_include_portfolio,
            'sitemap_changefreq' => $settings->sitemap_changefreq,
            'sitemap_priority' => $settings->sitemap_priority,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Seo')
                    ->tabs([
                        Tab::make('Judul Halaman')
                            ->schema([
                                Section::make('Format Judul Default')
                                    ->description('Placeholder yang dikenali: {page_title}, {site_name}, {separator}. Penanda lain dibuang otomatis (FR-025, FR-027).')
                                    ->schema([
                                        TextInput::make('title_separator')
                                            ->label('Pemisah Judul')
                                            ->required()
                                            ->maxLength(10),
                                        TextInput::make('default_title_format')
                                            ->label('Format Judul Default')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                                Section::make('Format Judul per Jenis Halaman')
                                    ->description('Kosongkan untuk memakai format default. Hanya jenis halaman yang benar-benar punya alamat publik di situs ini (FR-026).')
                                    ->schema(collect(SeoSettings::PAGE_TYPES)
                                        ->map(fn (string $label, string $key) => TextInput::make("page_title_formats.{$key}")
                                            ->label($label)
                                            ->maxLength(255))
                                        ->values()
                                        ->all())
                                    ->columns(2),
                            ]),
                        Tab::make('Meta & Kata Kunci')
                            ->schema([
                                Textarea::make('default_meta_description')
                                    ->label('Deskripsi Meta Default')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->helperText('Dipakai bila suatu halaman/konten belum punya deskripsi SEO sendiri. Disarankan ≤160 karakter.'),
                                TagsInput::make('meta_keywords')
                                    ->label('Kata Kunci Meta'),
                                TextInput::make('default_canonical_url')
                                    ->label('URL Kanonik Default')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Kosongkan untuk memakai URL halaman yang sedang dibuka.'),
                                TextInput::make('twitter_handle')
                                    ->label('Akun Twitter/X Situs')
                                    ->placeholder('@namaakun')
                                    ->maxLength(50),
                                Textarea::make('additional_head_meta')
                                    ->label('Meta Tag Tambahan')
                                    ->rows(4)
                                    ->helperText('Meta tag HTML bebas, dimuat apa adanya di bagian head.'),
                            ]),
                        Tab::make('Pengindeksan')
                            ->schema([
                                Toggle::make('allow_indexing')
                                    ->label('Izinkan Pengindeksan')
                                    ->helperText('Saat dimatikan, mesin pencari tidak akan mengindeks situs.'),
                                Toggle::make('allow_following')
                                    ->label('Izinkan Penelusuran Tautan')
                                    ->helperText('Saat dimatikan, mesin pencari tidak akan mengikuti tautan di situs.'),
                            ]),
                        Tab::make('Verifikasi Situs')
                            ->schema([
                                TextInput::make('verification_google')
                                    ->label('Google Search Console')
                                    ->maxLength(255),
                                TextInput::make('verification_bing')
                                    ->label('Bing Webmaster Tools')
                                    ->maxLength(255),
                                TextInput::make('verification_yandex')
                                    ->label('Yandex Webmaster')
                                    ->maxLength(255),
                                TextInput::make('verification_baidu')
                                    ->label('Baidu Webmaster Tools')
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                        Tab::make('Robots & Sitemap')
                            ->schema([
                                Textarea::make('robots_txt_content')
                                    ->label('Isi robots.txt')
                                    ->rows(6)
                                    ->extraInputAttributes(['class' => 'font-mono text-sm'])
                                    ->helperText('Gunakan {site_url} sebagai placeholder alamat situs. Kosongkan untuk memakai aturan bawaan yang aman (FR-035, FR-037).'),
                                Toggle::make('sitemap_enabled')
                                    ->label('Aktifkan Sitemap XML')
                                    ->live(),
                                Select::make('sitemap_changefreq')
                                    ->label('Frekuensi Perubahan Default')
                                    ->options(self::CHANGEFREQ_OPTIONS)
                                    ->native(false),
                                Select::make('sitemap_priority')
                                    ->label('Prioritas Default')
                                    ->options(self::PRIORITY_OPTIONS)
                                    ->native(false),
                                Toggle::make('sitemap_include_pages')
                                    ->label('Sertakan Halaman Kustom'),
                                Toggle::make('sitemap_include_articles')
                                    ->label('Sertakan Artikel'),
                                Toggle::make('sitemap_include_products')
                                    ->label('Sertakan Produk'),
                                Toggle::make('sitemap_include_portfolio')
                                    ->label('Sertakan Proyek Portfolio'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = app(SeoSettings::class);
        $settings->title_separator = $data['title_separator'];
        $settings->default_title_format = $data['default_title_format'];
        $settings->page_title_formats = array_filter($data['page_title_formats'] ?? []);
        $settings->default_meta_description = $data['default_meta_description'] ?? null;
        $settings->meta_keywords = $data['meta_keywords'] ?? [];
        $settings->default_canonical_url = $data['default_canonical_url'] ?? null;
        $settings->allow_indexing = $data['allow_indexing'] ?? true;
        $settings->allow_following = $data['allow_following'] ?? true;
        $settings->twitter_handle = $data['twitter_handle'] ?? null;
        $settings->additional_head_meta = $data['additional_head_meta'] ?? null;
        $settings->verification_google = $data['verification_google'] ?? null;
        $settings->verification_bing = $data['verification_bing'] ?? null;
        $settings->verification_yandex = $data['verification_yandex'] ?? null;
        $settings->verification_baidu = $data['verification_baidu'] ?? null;
        $settings->robots_txt_content = $data['robots_txt_content'] ?? null;
        $settings->sitemap_enabled = $data['sitemap_enabled'] ?? true;
        $settings->sitemap_include_pages = $data['sitemap_include_pages'] ?? true;
        $settings->sitemap_include_articles = $data['sitemap_include_articles'] ?? true;
        $settings->sitemap_include_products = $data['sitemap_include_products'] ?? true;
        $settings->sitemap_include_portfolio = $data['sitemap_include_portfolio'] ?? true;
        $settings->sitemap_changefreq = $data['sitemap_changefreq'] ?? null;
        $settings->sitemap_priority = $data['sitemap_priority'] ?? null;
        $settings->save();

        Notification::make()
            ->success()
            ->title('Pengaturan SEO tersimpan')
            ->send();
    }
}
