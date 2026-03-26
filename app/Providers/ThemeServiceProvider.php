<?php

namespace App\Providers;

use App\ACF\FieldRegistrar;
use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        parent::boot();

        app(FieldRegistrar::class)->register();
    }
}
