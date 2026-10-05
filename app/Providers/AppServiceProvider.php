<?php

namespace App\Providers;

use App\Services\Payment\MidtransGateway;
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
        /*
         * Gateway pembayaran dipilih berdasarkan konfigurasi.
         *
         * Bila MIDTRANS_SERVER_KEY diisi dan MIDTRANS_MODE=snap, pembayaran
         * diarahkan ke halaman Midtrans. Bila belum, gateway bekerja dalam mode
         * simulasi sehingga seluruh alur transaksi tetap dapat didemonstrasikan.
         * Pemanggil tidak perlu berubah karena keduanya memakai kontrak
         * PaymentGateway yang sama.
         */
        $this->app->bind(PaymentGateway::class, function () {
            $key = config('services.midtrans.server_key');
            $mode = config('services.midtrans.mode', 'simulation');

            if (filled($key) && $mode === 'snap') {
                return new MidtransGateway(
                    serverKey: $key,
                    mode: 'snap',
                    production: (bool) config('services.midtrans.production', false),
                );
            }

            return new MidtransGateway(serverKey: null, mode: 'simulation');
        });
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
