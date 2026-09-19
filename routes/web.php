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

    // Master Data
    Route::resource('komoditas', \App\Http\Controllers\KomoditasController::class)->except(['create', 'show', 'edit']);
    Route::resource('opt', \App\Http\Controllers\OptController::class)->except(['create', 'show', 'edit']);
    Route::resource('uppt', \App\Http\Controllers\UpptController::class)->except(['create', 'show', 'edit']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
