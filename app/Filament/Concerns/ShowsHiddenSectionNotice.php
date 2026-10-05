<?php

namespace App\Filament\Concerns;

use App\Enums\PublicSection;
use App\Filament\Pages\SectionVisibilitySettingsPage;
use App\Support\PageContent\SectionVisibility;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * Untuk halaman daftar menu yang mengatur isi section publik: menampilkan peringatan
 * di bawah judul bila satu atau lebih section terkait sedang disembunyikan dari situs.
 */
trait ShowsHiddenSectionNotice
{
    /**
     * Section publik yang isinya diatur lewat halaman ini.
     *
     * @return list<PublicSection>
     */
    abstract protected function relatedPublicSections(): array;

    public function getSubheading(): string|Htmlable|null
    {
        $hidden = array_values(array_filter(
            $this->relatedPublicSections(),
            fn (PublicSection $section): bool => ! SectionVisibility::shows($section),
        ));

        if ($hidden === []) {
            return parent::getSubheading();
        }

        $names = e(implode(', ', array_map(fn (PublicSection $section): string => $section->fullLabel(), $hidden)));
        $url = e(SectionVisibilitySettingsPage::getUrl());

        return new HtmlString(
            '<span data-hidden-section-notice class="font-medium text-warning-600 dark:text-warning-400">'
            ."Section ini sedang disembunyikan dari situs: {$names}. "
            .'<a href="'.$url.'" class="underline">Atur di Tampilan Section</a></span>'
        );
    }
}
