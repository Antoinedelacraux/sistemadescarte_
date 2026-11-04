<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
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
        Paginator::defaultView('vendor.pagination.default');

        if ($this->app->environment('local')) {
            $connection = (string) config('database.default');
            $database = config("database.connections.{$connection}.database");
            $sqliteSinCrear = $connection === 'sqlite'
                && $database !== ':memory:'
                && (! is_string($database) || ! is_file($database));

            if (! $sqliteSinCrear && Schema::hasTable('fundos') && ! Schema::hasColumn('fundos', 'nombre_completo')) {
                Artisan::call('migrate', ['--force' => true]);
            }
        }
    }
}
