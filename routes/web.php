<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KomoditasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\OptController;
use App\Http\Controllers\PengamatanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RiwayatPengamatanController;
use App\Http\Controllers\UpptController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/uppt/{uppt}/kecamatans', [UpptController::class, 'getKecamatans'])->name('api.uppt.kecamatans');
    Route::get('/opt', [OptController::class, 'index'])->name('opt.index');

    // Fitur Pengamatan (Input Data)
    Route::get('/pengamatan/riwayat', [PengamatanController::class, 'history'])->name('pengamatan.history');
    Route::get('/pengamatan/create', [PengamatanController::class, 'create'])->name('pengamatan.create');
    Route::post('/pengamatan', [PengamatanController::class, 'store'])->name('pengamatan.store');
    Route::get('/pengamatan/{pengamatan}/edit', [PengamatanController::class, 'edit'])->name('pengamatan.edit');
    Route::put('/pengamatan/{pengamatan}', [PengamatanController::class, 'update'])->name('pengamatan.update');
    Route::delete('/pengamatan/{pengamatan}', [PengamatanController::class, 'destroy'])->name('pengamatan.destroy');

    Route::middleware('can:manage-opt')->group(function () {
        Route::resource('opt', OptController::class)->except(['index', 'create', 'show', 'edit']);
        Route::post('opt/{opt}/jenis', [OptController::class, 'storeJenis'])->name('opt.jenis.store');
        Route::put('opt/{opt}/jenis/{jenisOpt}', [OptController::class, 'updateJenis'])->name('opt.jenis.update');
        Route::delete('opt/{opt}/jenis/{jenisOpt}', [OptController::class, 'destroyJenis'])->name('opt.jenis.destroy');
    });

    // Fitur Khusus Admin (Laporan & Master Data)
    Route::middleware('can:admin')->group(function () {
        Route::get('/data-pengamatan', [RiwayatPengamatanController::class, 'index'])->name('pengamatan.riwayat.index');

        // Laporan
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/triwulan', [LaporanController::class, 'quarterly'])->name('laporan.triwulan');
        Route::get('/laporan/tahunan', [LaporanController::class, 'historyTahunan'])->name('laporan.tahunan');
        Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
        Route::get('/laporan/triwulan/export/excel', [LaporanController::class, 'exportQuarterlyExcel'])->name('laporan.export.triwulan.excel');
        Route::get('/laporan/triwulan/export/pdf', [LaporanController::class, 'exportQuarterlyPdf'])->name('laporan.export.triwulan.pdf');
        Route::get('/laporan/tahunan/export/excel', [LaporanController::class, 'exportAnnualExcel'])->name('laporan.export.tahunan.excel');
        Route::get('/laporan/tahunan/export/pdf', [LaporanController::class, 'exportAnnualPdf'])->name('laporan.export.tahunan.pdf');

        // Master Data
        Route::resource('komoditas', KomoditasController::class)->parameters(['komoditas' => 'komoditas'])->except(['create', 'show', 'edit']);
        Route::resource('uppt', UpptController::class)->except(['create', 'show', 'edit']);
        Route::resource('pengguna', UserController::class)->parameters(['pengguna' => 'pengguna'])->except(['create', 'show', 'edit']);
        Route::patch('pengguna/{pengguna}/status', [UserController::class, 'updateStatus'])->name('pengguna.status');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
