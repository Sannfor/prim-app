<?php

namespace App\Providers;

use App\Services\Payment\MockPaymentGateway;
use App\Services\Payment\PaymentGateway;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // PRIM belum terhubung ke penyedia pembayaran nyata, sehingga gateway
        // yang dipakai adalah simulasi internal. Untuk beralih ke penyedia
        // sungguhan, cukup ganti binding di bawah ini.
        $this->app->bind(PaymentGateway::class, MockPaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Namespace view `layouts::` dipakai oleh atribut #[Layout('layouts::...')]
         * pada komponen Livewire. Namespace ini dipetakan ke resources/views
         * sehingga `layouts::admin` menunjuk ke resources/views/layouts/admin.blade.php.
         */
        $hints = View::getFinder()->getHints();

        if (! array_key_exists('layouts', $hints)) {
            View::addNamespace('layouts', resource_path('views'));
        }
    }
}
