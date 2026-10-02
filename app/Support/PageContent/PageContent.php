<?php

namespace App\Support\PageContent;

use App\Enums\CtaPlacement;
use App\Enums\PageBlockType;
use App\Enums\PageSection;
use App\Models\CallToAction;
use App\Models\PageBlock;
use App\Models\SectionHeading;
use App\Models\SectionItem;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Akses konten section & CTA untuk Blade. Dibaca langsung (tanpa cache) agar perubahan admin langsung tampil.
 */
class PageContent
{
    /**
     * Null bila section tidak punya item aktif, sehingga section tidak dirender.
     */
    public static function section(PageSection $section): ?SectionContent
    {
        $items = SectionItem::query()
            ->forSection($section)
            ->active()
            ->ordered()
            ->get();

        if ($items->isEmpty()) {
            return null;
        }

        if (! $section->supportsEmphasis()) {
            $items->each(fn (SectionItem $item) => $item->is_emphasized = false);
        }

        $heading = SectionHeading::query()->where('section', $section->value)->first();
        $fallback = DefaultPageContent::forCurrentSite(DefaultPageContent::headings()[$section->value]);

        $source = fn (string $key): mixed => $heading ? $heading->{$key} : ($fallback[$key] ?? null);

        return new SectionContent(
            title: $heading?->title ?? $fallback['title'],
            subtitle: $section->hasSubtitle() ? $source('subtitle') : null,
            items: $items,
            eyebrow: $section->hasEyebrow() ? $source('eyebrow') : null,
            featured: $section->hasFeaturedCard() ? [
                'image_path' => $source('featured_image_path'),
                'icon' => $source('featured_icon'),
                'title' => $source('featured_title'),
                'description' => $source('featured_description'),
            ] : ['image_path' => null, 'icon' => null, 'title' => null, 'description' => null],
        );
    }

    /**
     * Selalu mengembalikan blok; kunci yang hilang memakai nilai bawaan.
     */
    public static function block(PageBlockType $type): PageBlock
    {
        $row = PageBlock::query()->where('block', $type->value)->first();
        $defaults = DefaultPageContent::forCurrentSite(DefaultPageContent::blocks()[$type->value]);

        return new PageBlock([
            'block' => $type,
            'data' => [...$defaults, ...($row?->data ?? [])],
        ]);
    }

    /**
     * URL gambar unggahan admin; bila kosong atau berkasnya hilang dari disk, memakai gambar bawaan.
     */
    public static function imageUrl(?string $path, ?string $defaultAsset): ?string
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return $defaultAsset ? asset($defaultAsset) : null;
    }

    /**
     * Teks kaya dari admin, dibersihkan dari HTML berbahaya.
     */
    public static function richText(?string $html): HtmlString
    {
        return new HtmlString(HtmlSanitizer::clean($html));
    }

    public static function whatsappMessage(): string
    {
        return (string) static::block(PageBlockType::ContactInfo)->value('whatsapp_message');
    }

    /**
     * Selalu mengembalikan CTA; jika barisnya hilang, memakai teks bawaan.
     */
    public static function cta(CtaPlacement $placement): CallToAction
    {
        return CallToAction::query()->where('placement', $placement->value)->first()
            ?? new CallToAction([
                ...DefaultPageContent::forCurrentSite(DefaultPageContent::ctas()[$placement->value]),
                'placement' => $placement,
            ]);
    }

    /**
     * Teks admin apa adanya; satu-satunya HTML yang disisipkan adalah `<br>` untuk pindah baris.
     */
    public static function multiline(?string $text): HtmlString
    {
        $normalized = str_replace("\r\n", "\n", (string) $text);

        return new HtmlString(str_replace("\n", '<br>', e($normalized)));
    }

    public static function withProductName(?string $body, string $productName): HtmlString
    {
        return new HtmlString(str_replace('{produk}', e(Str::lower($productName)), e((string) $body)));
    }
}
