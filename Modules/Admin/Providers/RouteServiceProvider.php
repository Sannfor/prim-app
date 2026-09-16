<?php

namespace Modules\Admin\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Admin';

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
     * Proteksi role admin ditulis langsung di routes/web.php modul ini.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }
}
