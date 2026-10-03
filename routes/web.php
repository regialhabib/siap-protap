<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KomoditasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\OptController;
use App\Http\Controllers\PengamatanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UpptController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\IsAdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/uppt/{uppt}/kecamatans', [UpptController::class, 'getKecamatans'])->name('api.uppt.kecamatans');

    // Fitur Pengamatan (Input Data)
    Route::get('/pengamatan/create', [PengamatanController::class, 'create'])->name('pengamatan.create');
    Route::post('/pengamatan', [PengamatanController::class, 'store'])->name('pengamatan.store');
    Route::get('/pengamatan/{pengamatan}/edit', [PengamatanController::class, 'edit'])->name('pengamatan.edit');
    Route::put('/pengamatan/{pengamatan}', [PengamatanController::class, 'update'])->name('pengamatan.update');
    Route::delete('/pengamatan/{pengamatan}', [PengamatanController::class, 'destroy'])->name('pengamatan.destroy');

    // Fitur Khusus Admin (Laporan & Master Data)
    Route::middleware([IsAdminMiddleware::class])->group(function () {
        // Laporan
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');

        // Master Data
        Route::resource('komoditas', KomoditasController::class)->except(['create', 'show', 'edit']);
        Route::resource('opt', OptController::class)->except(['create', 'show', 'edit']);
        Route::resource('uppt', UpptController::class)->except(['create', 'show', 'edit']);
        Route::resource('pengguna', UserController::class)->parameters(['pengguna' => 'pengguna'])->except(['create', 'show', 'edit']);
        Route::patch('pengguna/{pengguna}/status', [UserController::class, 'updateStatus'])->name('pengguna.status');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
