<?php

use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['patch', 'put'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/foto', [ProfileController::class, 'destroyFoto'])->name('profile.foto.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route alias /profil sesuai modul referensi Bab 9.1
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/', [ProfilController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch', 'post'], '/', [ProfilController::class, 'update'])->name('update');
        Route::delete('/foto', [ProfilController::class, 'destroyFoto'])->name('foto.destroy');
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', fn () => view('guru.dashboard'))->name('dashboard');
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', fn () => view('siswa.dashboard'))->name('dashboard');
});

require __DIR__.'/auth.php';
