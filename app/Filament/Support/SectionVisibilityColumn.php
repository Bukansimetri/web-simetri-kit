<?php

namespace App\Filament\Support;

use App\Enums\PublicSection;
use App\Support\PageContent\SectionVisibility;
use Closure;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

/**
 * Kolom "Tayang" untuk tabel menu isi: badge "Disembunyikan" bila section baris itu sedang disembunyikan.
 */
class SectionVisibilityColumn
{
    public const HIDDEN = 'Disembunyikan';

    public const SHOWN = '—';

    /**
     * @param  Closure(Model): ?PublicSection  $resolve  section publik milik baris; null bila baris tidak punya toggle
     */
    public static function make(Closure $resolve): TextColumn
    {
        return TextColumn::make('visibility')
            ->label('Tayang')
            ->state(function (Model $record) use ($resolve): string {
                $section = $resolve($record);

                return $section !== null && ! SectionVisibility::shows($section) ? self::HIDDEN : self::SHOWN;
            })
            ->badge()
            ->color(fn (string $state): string => $state === self::HIDDEN ? 'warning' : 'gray');
    }
}
