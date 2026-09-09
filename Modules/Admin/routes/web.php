<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('admins', AdminController::class)->names('admin');
});

Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', function () {
        return view('admin::components.dashboard');
    })->name('admin.dashboard');
});