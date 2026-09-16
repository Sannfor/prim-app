<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

/**
 * Mendaftarkan komponen Livewire milik setiap modul PRIM.
 *
 * Setiap modul HMVC menaruh komponennya di:
 *   Modules\<Nama>\app\Livewire        (namespace Modules\<Nama>\Livewire)
 *   Modules\<Nama>\resources\views\livewire  (view, dipanggil eksplisit dari render())
 *
 * Karena namespace tersebut berada di luar App\Livewire, Livewire perlu diberi
 * tahu lokasinya. Komponen lalu dirujuk memakai nama bernamespace, misalnya
 * 'catalog::service-list'.
 */
class ModuleLivewireServiceProvider extends ServiceProvider
{
    /**
     * Peta alias modul => nama modul.
     *
     * @var array<string, string>
     */
    private const MODULES = [
        'catalog' => 'Catalog',
        'transaction' => 'Transaction',
        'subscription' => 'Subscription',
        'admin' => 'Admin',
    ];

    public function boot(): void
    {
        foreach (self::MODULES as $alias => $module) {
            Livewire::addNamespace(
                namespace: $alias,
                classNamespace: 'Modules\\'.$module.'\\Livewire',
                classPath: module_path($module, 'app/Livewire'),
                classViewPath: module_path($module, 'resources/views/livewire'),
            );
        }
    }
}
