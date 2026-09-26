<?php

namespace App\Providers\Filament;

use App\Settings\AppearanceSettings;
use App\Settings\SiteSettings;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Rmsramos\Activitylog\ActivitylogPlugin;
use TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn () => app(SiteSettings::class)->site_name ?: config('app.name'))
            ->brandLogo(fn () => static::brandAssetUrl(app(AppearanceSettings::class)->logo_path))
            ->favicon(fn () => static::brandAssetUrl(app(AppearanceSettings::class)->favicon_path))
            ->colors(fn () => [
                'primary' => app(AppearanceSettings::class)->primary_color ?: Color::Amber,
            ])
            ->darkMode(true)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString(
                    '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap">'
                ),
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn () => app(SiteSettings::class)->maintenance_mode
                    ? new HtmlString(
                        '<div style="background:#b91c1c;color:#fff;text-align:center;padding:8px 16px;font-size:14px;font-weight:600;">'
                        .'Mode Pemeliharaan aktif — pengunjung publik melihat halaman pemeliharaan.'
                        .'</div>'
                    )
                    : null,
            )
            ->navigationGroups([
                // Tanpa icon di level grup — tiap resource/page di bawahnya
                // sudah punya icon sendiri, dan Filament tidak mengizinkan
                // keduanya sekaligus (icon grup ATAU icon item, bukan dua-duanya).
                NavigationGroup::make('Konten Halaman'),
                NavigationGroup::make('Katalog'),
                NavigationGroup::make('Prospek & Pesan'),
                NavigationGroup::make('Blog'),
                NavigationGroup::make('Portfolio'),
                NavigationGroup::make('Karir'),
                NavigationGroup::make('Menu Builder'),
                NavigationGroup::make('Pengaturan Situs'),
                NavigationGroup::make('Sistem'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        hasAvatars: false,
                        slug: 'my-profile'
                    ),
                FilamentMediaManagerPlugin::make(),
                ActivitylogPlugin::make()
                    ->label('Log Aktivitas')
                    ->pluralLabel('Log Aktivitas')
                    ->navigationGroup('Sistem')
                    ->navigationIcon('heroicon-o-clock')
                    ->navigationSort(3)
                    ->authorize(fn () => auth()->user()?->hasRole('super_admin') ?? false),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private static function brandAssetUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
