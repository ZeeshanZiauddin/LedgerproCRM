<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\CalenderWidget;
use App\Filament\Widgets\MonthlyRevenueReport;
use App\Filament\Widgets\TopCustomer;
use App\Filament\Widgets\TopSalesPerson;
use Awcodes\Overlook\OverlookPlugin;
use Awcodes\Overlook\Widgets\OverlookWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use EightyNine\Reports\ReportsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Hasnayeen\Themes\ThemesPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;
use lockscreen\FilamentLockscreen\Lockscreen;
use lockscreen\FilamentLockscreen\Http\Middleware\Locker;
use lockscreen\FilamentLockscreen\Http\Middleware\LockerTimer;
use Rmsramos\Activitylog\ActivitylogPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogPlugin;
use TomatoPHP\FilamentNotes\FilamentNotesPlugin;
use TomatoPHP\FilamentPWA\FilamentPWAPlugin;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('assets/favicon.ico'))
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                OverlookWidget::class,
                TopSalesPerson::class,
                TopCustomer::class,
                MonthlyRevenueReport::class,
            ])
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
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class,
                LockerTimer::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                FilamentPWAPlugin::make(),
                FilamentProgressbarPlugin::make()->color('#29b'),
                new Lockscreen(),
                FilamentAuthenticationLogPlugin::make(),
                ActivitylogPlugin::make()
                    ->authorize(
                        fn() => auth()->user()->hasRole('pannel_user')
                    ),
                FilamentApexChartsPlugin::make(),
                SpotlightPlugin::make(),
                QuickCreatePlugin::make()
                    ->includes([
                        \App\Filament\Resources\InquiryResource::class,
                        \App\Filament\Resources\CardResource::class,
                        \App\Filament\Resources\ReceiptResource::class,
                        \App\Filament\Resources\CustomerResource::class,
                        \App\Filament\Resources\SupplierResource::class,
                        \App\Filament\Resources\AirlineResource::class,
                        \App\Filament\Resources\CardResource::class,
                    ])
                    ->alwaysShowModal()
                    ->keyBindings(['command+shift+a', 'ctrl+m']),

                ThemesPlugin::make(),
                FilamentBackgroundsPlugin::make()
                    ->remember(900),
                OverlookPlugin::make()
                    ->sort(2)
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                        'lg' => 4,
                        'xl' => 5,
                        '2xl' => null,
                    ]),
                FilamentNotesPlugin::make()
                    ->useStatus()
                    ->useGroups()->useUserAccess(),
                FilamentEditProfilePlugin::make()
                    ->setTitle('My Profile')
                    ->setNavigationLabel('My Profile')
                    ->setNavigationGroup('User Management')
                    ->setIcon('heroicon-o-user')
                    ->setSort(0)
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                        rules: 'mimes:jpeg,png|max:1024'
                    ),

            ])
            ->authMiddleware([
                Authenticate::class,
                Locker::class,
            ])
            ->sidebarCollapsibleOnDesktop()
        ;
    }
}
