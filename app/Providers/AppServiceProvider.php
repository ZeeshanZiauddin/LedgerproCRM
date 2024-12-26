<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        FilamentAsset::register([
            Js::make('cardRepeator', resource_path('js/app/cardRepeator.js')),
        ]);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Filament::serving(function () {
            Filament::registerUserMenuItems([
                'test' => \Filament\Navigation\UserMenuItem::make()
                    ->label('My Profile')
                    ->url('/admin/edit-profile')
                    ->icon('heroicon-m-user-circle'), // Change icon if desired
            ]);
        });
    }
}
