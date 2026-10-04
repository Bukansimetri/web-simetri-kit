<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

/**
 * Satu menu "Banner" berisi tab Slider Beranda dan Banner Halaman.
 */
class BannerCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Banner';

    protected static ?string $navigationGroup = 'Beranda';

    protected static ?int $navigationSort = 1;

    protected static ?string $clusterBreadcrumb = 'Banner';

    protected static ?string $slug = 'banner';
}
