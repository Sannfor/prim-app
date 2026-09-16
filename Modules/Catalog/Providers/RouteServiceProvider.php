<?php

namespace Modules\Catalog\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Catalog';

    /**
     * Define the routes for the module.
     */
    public function map(): void
    {
        $this->mapWebRoutes();
    }

    /**
     * Route web modul: menerima session state dan proteksi CSRF.
     *
     * Route katalog bersifat PUBLIK, sehingga tidak ada middleware auth di sini.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }
}
