<?php

use App\Concerns\CachesPublicPages;
use App\Support\PageContent\JobDescriptionConverter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deskripsi lowongan kini teks berformat (HTML). Teks polos lama diubah menjadi paragraf agar tampilan tidak berubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('job_openings')) {
            return;
        }

        DB::table('job_openings')->select(['id', 'description'])->orderBy('id')->each(function (object $row): void {
            $html = JobDescriptionConverter::toHtml((string) $row->description);

            if ($html !== $row->description) {
                DB::table('job_openings')->where('id', $row->id)->update(['description' => $html]);
            }
        });

        (new class
        {
            use CachesPublicPages;
        })::bumpPublicPageVersion();
    }

    public function down(): void
    {
        //
    }
};
