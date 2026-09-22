<?php

namespace App\Console\Commands;

use App\Models\DemoSeedRecord;
use App\Models\MenuItem;
use App\Models\PortfolioCategory;
use App\Models\Product;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:seed')]
#[Description('Mengisi konten contoh (Layanan, Tim, Testimoni, Portfolio) untuk demo penjualan — tidak pernah berjalan otomatis saat setup instalasi standar')]
class DemoSeedCommand extends Command
{
    /**
     * Label tampil per model, dipakai untuk ringkasan hasil (kontrak:
     * contracts/cli-command-contract.md).
     *
     * @var array<class-string, string>
     */
    private const LABELS = [
        PortfolioCategory::class => 'Kategori Portfolio',
        Product::class => 'Layanan',
        TeamMember::class => 'Tim',
        Testimonial::class => 'Testimoni',
        MenuItem::class => 'Menu Builder',
    ];

    public function handle(): int
    {
        $alreadySeededBefore = collect(self::LABELS)->keys()
            ->mapWithKeys(fn (string $model) => [$model => DemoSeedRecord::alreadySeeded($model)]);

        $this->call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

        foreach (self::LABELS as $model => $label) {
            if ($alreadySeededBefore[$model]) {
                $this->components->warn("{$label} — dilewati (sudah pernah diisi).");
            } else {
                $this->components->task($label, fn () => true);
            }
        }

        $this->components->info('Pengisian konten demo selesai.');

        return self::SUCCESS;
    }
}
