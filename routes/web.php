<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengamatanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Fitur Pengamatan (Input Data)
    Route::get('/pengamatan/create', [PengamatanController::class, 'create'])->name('pengamatan.create');
    Route::post('/pengamatan', [PengamatanController::class, 'store'])->name('pengamatan.store');
    Route::get('/pengamatan/{pengamatan}/edit', [PengamatanController::class, 'edit'])->name('pengamatan.edit');
    Route::put('/pengamatan/{pengamatan}', [PengamatanController::class, 'update'])->name('pengamatan.update');
    Route::delete('/pengamatan/{pengamatan}', [PengamatanController::class, 'destroy'])->name('pengamatan.destroy');

    // Fitur Khusus Admin (Laporan & Master Data)
    Route::middleware([\App\Http\Middleware\IsAdminMiddleware::class])->group(function () {
        // Laporan
        Route::get('/laporan', [App\Http\Controllers\LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export/excel', [App\Http\Controllers\LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('/laporan/export/pdf', [App\Http\Controllers\LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');

        // Master Data
        Route::resource('komoditas', \App\Http\Controllers\KomoditasController::class)->except(['create', 'show', 'edit']);
        Route::resource('opt', \App\Http\Controllers\OptController::class)->except(['create', 'show', 'edit']);
        Route::resource('uppt', \App\Http\Controllers\UpptController::class)->except(['create', 'show', 'edit']);
        Route::resource('pengguna', \App\Http\Controllers\UserController::class)->parameters(['pengguna' => 'pengguna'])->except(['create', 'show', 'edit']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
