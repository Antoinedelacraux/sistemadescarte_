<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('local')) {
            if (\Illuminate\Support\Facades\Schema::hasTable('fundos') && !\Illuminate\Support\Facades\Schema::hasColumn('fundos', 'nombre_completo')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
        }
    }
}
