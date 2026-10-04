<?php

namespace App\Support\PageContent;

use App\Enums\CtaPlacement;
use App\Enums\PageBlockType;
use App\Enums\PageSection;
use App\Models\CallToAction;
use App\Models\PageBlock;
use App\Models\SectionHeading;
use App\Models\SectionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menanam konten bawaan secara idempoten: section yang sudah punya judul, blok, dan CTA yang sudah ada tidak disentuh
 * (blok hanya ditambah kolom baru yang belum ada).
 * Isi Tentang Kami dipindahkan dari pengaturan lama (grup `about_page`) bila ada, agar tampilan tidak berubah.
 */
class PageContentInstaller
{
    public static function install(): void
    {
        $legacy = self::legacyAboutSettings();

        foreach (PageSection::cases() as $section) {
            self::installSection($section, $legacy);
        }

        foreach (PageBlockType::cases() as $block) {
            $data = DefaultPageContent::forCurrentSite(self::blockData($block, $legacy));
            $row = PageBlock::query()->firstOrCreate(['block' => $block->value], ['data' => $data]);

            self::addMissingBlockKeys($row, $data);
        }

        foreach (CtaPlacement::cases() as $placement) {
            CallToAction::query()->firstOrCreate(
                ['placement' => $placement->value],
                DefaultPageContent::forCurrentSite(DefaultPageContent::ctas()[$placement->value]),
            );
        }
    }

    /**
     * @param  array<string, mixed>  $legacy
     */
    private static function installSection(PageSection $section, array $legacy): void
    {
        if (SectionHeading::query()->where('section', $section->value)->exists()) {
            return;
        }

        DB::transaction(function () use ($section, $legacy): void {
            SectionHeading::query()->create([
                'section' => $section,
                ...DefaultPageContent::forCurrentSite(self::headingData($section, $legacy)),
            ]);

            foreach (self::itemData($section, $legacy) as $order => $template) {
                $item = DefaultPageContent::forCurrentSite($template);

                SectionItem::query()->create([
                    'section' => $section,
                    'icon' => $item['icon'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'is_active' => true,
                    'is_emphasized' => $item['is_emphasized'],
                    'order' => $order,
                ]);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $legacy
     * @return array<string, ?string>
     */
    private static function headingData(PageSection $section, array $legacy): array
    {
        $defaults = DefaultPageContent::headings()[$section->value];

        return match ($section) {
            PageSection::AboutMission => [
                'title' => self::pick($legacy, 'misi_heading', $defaults['title']),
                'subtitle' => self::keep($legacy, 'misi_subtext', $defaults['subtitle']),
                'eyebrow' => self::keep($legacy, 'misi_eyebrow', $defaults['eyebrow']),
            ],
            PageSection::AboutValues => [
                'title' => self::pick($legacy, 'nilai_heading', $defaults['title']),
                'subtitle' => self::keep($legacy, 'nilai_subtext', $defaults['subtitle']),
                'featured_image_path' => self::keep($legacy, 'nilai_featured_image_path', $defaults['featured_image_path']),
                'featured_icon' => self::keep($legacy, 'nilai_featured_icon', $defaults['featured_icon']),
                'featured_title' => self::keep($legacy, 'nilai_featured_title', $defaults['featured_title']),
                'featured_description' => self::keep($legacy, 'nilai_featured_description', $defaults['featured_description']),
            ],
            default => $defaults,
        };
    }

    /**
     * @param  array<string, mixed>  $legacy
     * @return list<array{icon: ?string, title: string, description: string, is_emphasized: bool}>
     */
    private static function itemData(PageSection $section, array $legacy): array
    {
        $fromLegacy = match ($section) {
            PageSection::AboutMission => self::legacyList($legacy, 'misi_items'),
            PageSection::AboutValues => self::legacyList($legacy, 'nilai_items'),
            PageSection::AboutTrust => self::legacyList($legacy, 'trust_items'),
            default => null,
        };

        if ($fromLegacy === null) {
            return DefaultPageContent::items()[$section->value];
        }

        return array_map(fn (array $row): array => [
            'icon' => $row['icon'] ?? null,
            'title' => (string) ($section === PageSection::AboutTrust ? ($row['value'] ?? '') : ($row['title'] ?? '')),
            'description' => (string) ($section === PageSection::AboutTrust ? ($row['label'] ?? '') : ($row['description'] ?? '')),
            'is_emphasized' => false,
        ], $fromLegacy);
    }

    /**
     * @param  array<string, mixed>  $legacy
     * @return array<string, ?string>
     */
    private static function blockData(PageBlockType $block, array $legacy): array
    {
        $defaults = DefaultPageContent::blocks()[$block->value];

        return match ($block) {
            PageBlockType::AboutHero => [
                'image_path' => self::keep($legacy, 'hero_image_path', $defaults['image_path']),
                'title' => $defaults['title'],
                'subtitle' => self::keep($legacy, 'hero_subtitle', $defaults['subtitle']),
            ],
            PageBlockType::AboutWhoWeAre => [
                'image_path' => self::keep($legacy, 'siapa_kami_image_path', $defaults['image_path']),
                'badge_text' => self::keep($legacy, 'siapa_kami_badge_text', $defaults['badge_text']),
                'eyebrow' => self::keep($legacy, 'siapa_kami_eyebrow', $defaults['eyebrow']),
                'heading' => self::keep($legacy, 'siapa_kami_heading', $defaults['heading']),
                'body' => self::keep($legacy, 'siapa_kami_body', $defaults['body']),
                'quote' => self::keep($legacy, 'siapa_kami_quote', $defaults['quote']),
            ],
            PageBlockType::AboutVision => [
                'eyebrow' => self::keep($legacy, 'visi_eyebrow', $defaults['eyebrow']),
                'heading' => self::keep($legacy, 'visi_heading', $defaults['heading']),
                'subtext' => self::keep($legacy, 'visi_subtext', $defaults['subtext']),
            ],
            default => $defaults,
        };
    }

    /**
     * Kolom baru pada blok yang sudah tersimpan diisi nilai bawaan; nilai yang sudah ada (termasuk kosong) tidak disentuh.
     *
     * @param  array<string, mixed>  $defaults
     */
    private static function addMissingBlockKeys(PageBlock $row, array $defaults): void
    {
        $missing = array_diff_key($defaults, $row->data ?? []);

        if ($missing !== []) {
            $row->update(['data' => [...($row->data ?? []), ...$missing]]);
        }
    }

    /**
     * Nilai pengaturan lama bila ada; `null` yang tersimpan dipertahankan (admin mengosongkannya).
     *
     * @param  array<string, mixed>  $legacy
     */
    private static function keep(array $legacy, string $key, ?string $default): ?string
    {
        return array_key_exists($key, $legacy) ? ($legacy[$key] === null ? null : (string) $legacy[$key]) : $default;
    }

    /**
     * Untuk kolom wajib: kosong → nilai bawaan.
     *
     * @param  array<string, mixed>  $legacy
     */
    private static function pick(array $legacy, string $key, string $default): string
    {
        $value = $legacy[$key] ?? null;

        return filled($value) ? (string) $value : $default;
    }

    /**
     * @param  array<string, mixed>  $legacy
     * @return list<array<string, mixed>>|null
     */
    private static function legacyList(array $legacy, string $key): ?array
    {
        if (! array_key_exists($key, $legacy)) {
            return null;
        }

        $value = $legacy[$key];
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }

    /**
     * Baca langsung dari tabel `settings` (tanpa kelas settings, agar tetap jalan setelah kelasnya dihapus).
     *
     * @return array<string, mixed>
     */
    private static function legacyAboutSettings(): array
    {
        if (! Schema::hasTable('settings')) {
            return [];
        }

        return DB::table('settings')
            ->where('group', 'about_page')
            ->pluck('payload', 'name')
            ->map(fn (mixed $payload): mixed => json_decode((string) $payload, true))
            ->all();
    }
}
