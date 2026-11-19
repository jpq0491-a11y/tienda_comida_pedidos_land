<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

// Panel de administración: requiere sesión iniciada y pasar el Gate 'administrar'
Route::middleware(['auth', 'can:administrar'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.inicio')->name('inicio');
    Route::resource('categorias', CategoriaController::class)->except('show');
    Route::resource('productos', ProductoController::class)->except('show');

    // Aquí irá el panel de pedidos
});

require __DIR__.'/settings.php';
