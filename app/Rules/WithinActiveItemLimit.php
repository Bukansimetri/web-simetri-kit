<?php

namespace App\Rules;

use App\Enums\PageSection;
use App\Models\SectionItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Menolak pengaktifan item bila jumlah item aktif section sudah mencapai batas desainnya.
 */
class WithinActiveItemLimit implements ValidationRule
{
    public function __construct(
        public PageSection $section,
        public ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $activeCount = SectionItem::query()
            ->forSection($this->section)
            ->active()
            ->when($this->ignoreId, fn ($query) => $query->whereKeyNot($this->ignoreId))
            ->count();

        if ($activeCount >= $this->section->maxActiveItems()) {
            $fail("Section \"{$this->section->fullLabel()}\" maksimal {$this->section->maxActiveItems()} item aktif. Nonaktifkan item lain terlebih dahulu.");
        }
    }
}
