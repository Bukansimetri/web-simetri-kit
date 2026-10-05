<?php

namespace App\Support\PageContent;

use App\Enums\CustomPageTemplate;
use App\Models\CustomPage;
use Illuminate\Support\Facades\Schema;

/**
 * Menanam halaman Kebijakan Privasi dan Syarat & Ketentuan secara idempoten:
 * halaman dengan slug yang sudah ada (termasuk yang sudah diedit admin) tidak disentuh.
 */
class LegalPageInstaller
{
    public static function install(): void
    {
        if (! Schema::hasColumn('custom_pages', 'template')) {
            return;
        }

        foreach (DefaultLegalPages::pages() as $page) {
            if (CustomPage::query()->where('slug', $page['slug'])->exists()) {
                continue;
            }

            $values = DefaultLegalPages::forCurrentSite($page);

            CustomPage::query()->create([
                'title' => $values['title'],
                'slug' => $values['slug'],
                'template' => CustomPageTemplate::Legal,
                'content' => null,
                'legal' => $values['legal'],
                'meta_description' => $values['meta_description'],
            ]);
        }
    }
}
