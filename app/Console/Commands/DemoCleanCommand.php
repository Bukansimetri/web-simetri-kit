<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\DemoSeedRecord;
use App\Models\MenuItem;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Signature('demo:clean')]
#[Description('Menghapus seluruh konten demo (Banner, Layanan, Tim, Testimoni, Portfolio) tanpa menyentuh data yang ditambahkan admin')]
class DemoCleanCommand extends Command
{
    public function handle(): int
    {
        // Banner tidak punya dependensi ke entitas demo lain, jadi aman
        // dibersihkan di langkah mana pun (022-banner-hero-slider).
        $this->deleteTrackedRecords(Banner::class, 'Banner');

        // Urutan anak → induk (data-model.md §Urutan operasi) supaya tidak
        // melanggar foreign key dan tidak meninggalkan referensi rusak.
        $this->deleteTrackedRecords(PortfolioProject::class, 'Proyek Portfolio');
        $this->deleteTrackedRecords(Testimonial::class, 'Testimoni');
        $this->deleteTrackedRecords(TeamMember::class, 'Tim');
        $this->deleteTrackedRecords(Product::class, 'Layanan');
        $this->deletePortfolioCategoriesIfUnused();
        $this->deleteTrackedRecords(MenuItem::class, 'Menu Builder');

        $this->components->info('Pembersihan konten demo selesai.');

        return self::SUCCESS;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function deleteTrackedRecords(string $modelClass, string $label): void
    {
        $ids = $this->trackedIds($modelClass);

        if ($ids->isEmpty()) {
            return;
        }

        $modelClass::query()->whereIn('id', $ids)->delete();
        $this->forgetManifest($modelClass, $ids);

        $this->components->task($label, fn () => true);
    }

    /**
     * PortfolioCategory demo HANYA dihapus bila tidak ada PortfolioProject
     * lain (demo maupun bukan) yang masih memakainya (FR-006, research.md #5)
     * — proyek portfolio demo sudah dihapus di langkah sebelumnya, jadi
     * pengecekan ini sekarang murni mendeteksi pemakaian oleh proyek asli.
     */
    private function deletePortfolioCategoriesIfUnused(): void
    {
        $ids = $this->trackedIds(PortfolioCategory::class);

        if ($ids->isEmpty()) {
            return;
        }

        $unusedIds = $ids->reject(
            fn (int $id) => PortfolioProject::query()->where('portfolio_category_id', $id)->exists()
        );

        if ($unusedIds->isEmpty()) {
            return;
        }

        PortfolioCategory::query()->whereIn('id', $unusedIds)->delete();
        $this->forgetManifest(PortfolioCategory::class, $unusedIds);

        $this->components->task('Kategori Portfolio', fn () => true);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return Collection<int, int>
     */
    private function trackedIds(string $modelClass): Collection
    {
        return DemoSeedRecord::query()
            ->where('seedable_type', (new $modelClass)->getMorphClass())
            ->pluck('seedable_id');
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  Collection<int, int>  $ids
     */
    private function forgetManifest(string $modelClass, Collection $ids): void
    {
        DemoSeedRecord::query()
            ->where('seedable_type', (new $modelClass)->getMorphClass())
            ->whereIn('seedable_id', $ids)
            ->delete();
    }
}
