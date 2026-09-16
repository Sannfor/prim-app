<?php

use App\Console\Commands\ExpireTransactions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Bersihkan transaksi kedaluwarsa dan akhiri langganan yang sudah lewat.
Schedule::command(ExpireTransactions::class)->hourly();
