<?php

namespace App\Filament\Pages;

use App\Enums\PublicSection;
use App\Support\PageContent\SectionVisibility;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SectionVisibilitySettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = 'Pengaturan Situs';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Tampilan Section';

    protected static ?string $title = 'Tampilan Section';

    protected static string $view = 'filament.pages.settings-form-page';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $state = [];

        foreach (PublicSection::cases() as $section) {
            $state[self::field($section)] = SectionVisibility::shows($section);
        }

        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        $groups = [];

        foreach (PublicSection::pages() as $page) {
            $groups[] = Section::make(fn (): string => self::groupTitle($page))
                ->description('Matikan untuk menyembunyikan section dari situs. Isinya tetap tersimpan.')
                ->schema(array_map(
                    fn (PublicSection $section): Toggle => Toggle::make(self::field($section))
                        ->label($section->label())
                        ->hintAction(
                            Action::make('edit_'.$section->name)
                                ->label('Edit isi')
                                ->icon('heroicon-o-pencil-square')
                                ->url($section->contentUrl()),
                        ),
                    PublicSection::forPage($page),
                ))
                ->columns(1);
        }

        return $form->schema($groups)->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $hidden = array_values(array_filter(
            PublicSection::cases(),
            fn (PublicSection $section): bool => ! ($data[self::field($section)] ?? true),
        ));

        SectionVisibility::setHidden($hidden);

        Notification::make()
            ->success()
            ->title('Tampilan section tersimpan')
            ->send();
    }

    /**
     * Judul kelompok halaman, dihitung ulang setiap render agar jumlah tersembunyi ikut berubah setelah simpan.
     */
    public static function groupTitle(string $page): string
    {
        $hidden = count(array_filter(
            SectionVisibility::hiddenSections(),
            fn (PublicSection $section): bool => $section->page() === $page,
        ));

        return $hidden > 0 ? "{$page} · {$hidden} disembunyikan" : $page;
    }

    /**
     * Nama field form (kunci enum memuat titik dan strip, jadi dipakai nama case).
     */
    public static function field(PublicSection $section): string
    {
        return 'show_'.$section->name;
    }
}
