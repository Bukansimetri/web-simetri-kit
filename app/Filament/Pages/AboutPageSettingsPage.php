<?php

namespace App\Filament\Pages;

use App\Settings\AboutPageSettings;
use App\Support\ImageUploads;
use App\Support\MaterialSymbolsIcons;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class AboutPageSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $navigationGroup = 'Konten Halaman';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Halaman Tentang Kami';

    protected static ?string $title = 'Halaman Tentang Kami';

    protected static string $view = 'filament.pages.about-page-settings-page';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(AboutPageSettings::class);

        $this->form->fill([
            'hero_image_path' => $settings->hero_image_path,
            'hero_subtitle' => $settings->hero_subtitle,
            'siapa_kami_image_path' => $settings->siapa_kami_image_path,
            'siapa_kami_badge_text' => $settings->siapa_kami_badge_text,
            'siapa_kami_eyebrow' => $settings->siapa_kami_eyebrow,
            'siapa_kami_heading' => $settings->siapa_kami_heading,
            'siapa_kami_body' => $settings->siapa_kami_body,
            'siapa_kami_quote' => $settings->siapa_kami_quote,
            'visi_eyebrow' => $settings->visi_eyebrow,
            'visi_heading' => $settings->visi_heading,
            'visi_subtext' => $settings->visi_subtext,
            'misi_eyebrow' => $settings->misi_eyebrow,
            'misi_heading' => $settings->misi_heading,
            'misi_subtext' => $settings->misi_subtext,
            'misi_items' => $settings->misiItems(),
            'nilai_heading' => $settings->nilai_heading,
            'nilai_subtext' => $settings->nilai_subtext,
            'nilai_featured_image_path' => $settings->nilai_featured_image_path,
            'nilai_featured_icon' => $settings->nilai_featured_icon,
            'nilai_featured_title' => $settings->nilai_featured_title,
            'nilai_featured_description' => $settings->nilai_featured_description,
            'nilai_items' => $settings->nilaiItems(),
            'trust_items' => $settings->trustItems(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Hero')
                    ->description('Banner besar di bagian paling atas halaman.')
                    ->schema([
                        FileUpload::make('hero_image_path')
                            ->label('Gambar Latar')
                            ->image()
                            ->disk('public')
                            ->directory('about-page')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'about-page', maxWidth: 1920)),
                        Textarea::make('hero_subtitle')
                            ->label('Subjudul')
                            ->rows(2)
                            ->maxLength(500),
                    ]),
                Section::make('Siapa Kami')
                    ->schema([
                        FileUpload::make('siapa_kami_image_path')
                            ->label('Gambar')
                            ->image()
                            ->disk('public')
                            ->directory('about-page')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'about-page', maxWidth: 1000)),
                        TextInput::make('siapa_kami_badge_text')
                            ->label('Teks Badge Overlay')
                            ->maxLength(120),
                        TextInput::make('siapa_kami_eyebrow')
                            ->label('Label Kecil')
                            ->maxLength(60),
                        TextInput::make('siapa_kami_heading')
                            ->label('Judul')
                            ->maxLength(255),
                        RichEditor::make('siapa_kami_body')
                            ->label('Paragraf')
                            ->toolbarButtons(['bold', 'italic'])
                            ->helperText('Gunakan {app_name} untuk menampilkan nama aplikasi secara otomatis.')
                            ->maxLength(1000),
                        RichEditor::make('siapa_kami_quote')
                            ->label('Kutipan')
                            ->toolbarButtons(['bold', 'italic'])
                            ->maxLength(500),
                    ]),
                Section::make('Visi')
                    ->schema([
                        TextInput::make('visi_eyebrow')
                            ->label('Label Kecil')
                            ->maxLength(60),
                        Textarea::make('visi_heading')
                            ->label('Pernyataan Visi')
                            ->rows(3)
                            ->maxLength(500),
                        Textarea::make('visi_subtext')
                            ->label('Subteks')
                            ->rows(2)
                            ->maxLength(500),
                    ]),
                Section::make('Misi')
                    ->schema([
                        TextInput::make('misi_eyebrow')
                            ->label('Label Kecil')
                            ->maxLength(60),
                        TextInput::make('misi_heading')
                            ->label('Judul')
                            ->maxLength(255),
                        Textarea::make('misi_subtext')
                            ->label('Subteks')
                            ->rows(2)
                            ->maxLength(500),
                        Repeater::make('misi_items')
                            ->label('Daftar Misi')
                            ->helperText('Persis 5 item. 3 item pertama tampil di kolom kiri, 2 sisanya di kolom kanan.')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(120),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(500),
                            ])
                            ->minItems(5)
                            ->maxItems(5)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(1),
                    ]),
                Section::make('Nilai-Nilai')
                    ->schema([
                        TextInput::make('nilai_heading')
                            ->label('Judul')
                            ->maxLength(255),
                        Textarea::make('nilai_subtext')
                            ->label('Subteks')
                            ->rows(2)
                            ->maxLength(500),
                        FileUpload::make('nilai_featured_image_path')
                            ->label('Gambar Kartu Besar')
                            ->image()
                            ->disk('public')
                            ->directory('about-page')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'about-page', maxWidth: 1200)),
                        Select::make('nilai_featured_icon')
                            ->label('Ikon Kartu Besar')
                            ->options(MaterialSymbolsIcons::selectOptions())
                            ->allowHtml()
                            ->searchable()
                            ->native(false),
                        TextInput::make('nilai_featured_title')
                            ->label('Judul Kartu Besar')
                            ->maxLength(160),
                        Textarea::make('nilai_featured_description')
                            ->label('Deskripsi Kartu Besar')
                            ->rows(3)
                            ->maxLength(500),
                        Repeater::make('nilai_items')
                            ->label('Kartu Nilai')
                            ->helperText('Persis 3 item.')
                            ->schema([
                                Select::make('icon')
                                    ->label('Ikon')
                                    ->options(MaterialSymbolsIcons::selectOptions())
                                    ->allowHtml()
                                    ->searchable()
                                    ->native(false)
                                    ->required(),
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(120),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(500),
                            ])
                            ->minItems(3)
                            ->maxItems(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(1),
                    ]),
                Section::make('Trust Strip')
                    ->schema([
                        Repeater::make('trust_items')
                            ->label('Statistik')
                            ->helperText('Persis 3 item.')
                            ->schema([
                                Select::make('icon')
                                    ->label('Ikon')
                                    ->options(MaterialSymbolsIcons::selectOptions())
                                    ->allowHtml()
                                    ->searchable()
                                    ->native(false)
                                    ->required(),
                                TextInput::make('value')
                                    ->label('Nilai')
                                    ->required()
                                    ->maxLength(60),
                                TextInput::make('label')
                                    ->label('Label')
                                    ->required()
                                    ->maxLength(120),
                            ])
                            ->minItems(3)
                            ->maxItems(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(3),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = app(AboutPageSettings::class);
        $settings->hero_image_path = $data['hero_image_path'] ?? null;
        $settings->hero_subtitle = $data['hero_subtitle'] ?? null;
        $settings->siapa_kami_image_path = $data['siapa_kami_image_path'] ?? null;
        $settings->siapa_kami_badge_text = $data['siapa_kami_badge_text'] ?? null;
        $settings->siapa_kami_eyebrow = $data['siapa_kami_eyebrow'] ?? null;
        $settings->siapa_kami_heading = $data['siapa_kami_heading'] ?? null;
        $settings->siapa_kami_body = $data['siapa_kami_body'] ?? null;
        $settings->siapa_kami_quote = $data['siapa_kami_quote'] ?? null;
        $settings->visi_eyebrow = $data['visi_eyebrow'] ?? null;
        $settings->visi_heading = $data['visi_heading'] ?? null;
        $settings->visi_subtext = $data['visi_subtext'] ?? null;
        $settings->misi_eyebrow = $data['misi_eyebrow'] ?? null;
        $settings->misi_heading = $data['misi_heading'] ?? null;
        $settings->misi_subtext = $data['misi_subtext'] ?? null;
        $settings->misi_items = json_encode($data['misi_items'] ?? []);
        $settings->nilai_heading = $data['nilai_heading'] ?? null;
        $settings->nilai_subtext = $data['nilai_subtext'] ?? null;
        $settings->nilai_featured_image_path = $data['nilai_featured_image_path'] ?? null;
        $settings->nilai_featured_icon = $data['nilai_featured_icon'] ?? null;
        $settings->nilai_featured_title = $data['nilai_featured_title'] ?? null;
        $settings->nilai_featured_description = $data['nilai_featured_description'] ?? null;
        $settings->nilai_items = json_encode($data['nilai_items'] ?? []);
        $settings->trust_items = json_encode($data['trust_items'] ?? []);
        $settings->save();

        Cache::forget('public-page:tentang-kami');

        Notification::make()
            ->success()
            ->title('Pengaturan halaman Tentang Kami tersimpan')
            ->send();
    }
}
