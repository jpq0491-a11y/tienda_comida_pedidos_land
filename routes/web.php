<?php

use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;

// Landing de la tienda (Blade). Antes era Route::inertia('/', 'Welcome');
// se conserva el nombre 'home' porque el starter kit redirige a esa ruta
Route::get('/', InicioController::class)->name('home');

// Menú público agrupado por categoría
Route::get('/menu', MenuController::class)->name('menu');

// Carrito de invitado (se guarda en la sesión)
Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/{producto}', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::patch('/carrito/{producto}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
Route::delete('/carrito/{producto}', [CarritoController::class, 'quitar'])->name('carrito.quitar');

// Checkout: datos del cliente y confirmación del pedido
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
// {pedido:folio}: Laravel busca el pedido por su folio en lugar del id (404 si no existe)
Route::get('/pedido/{pedido:folio}', [CheckoutController::class, 'confirmacion'])->name('pedido.confirmacion');

// Panel de administración: requiere sesión iniciada y pasar el Gate 'administrar'
Route::middleware(['auth', 'can:administrar'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.inicio')->name('inicio');
    Route::resource('categorias', CategoriaController::class)->except('show');
    // CRUD completo (con show), como pide la rúbrica
    Route::resource('productos', ProductoController::class);

    // Panel de pedidos: el admin no crea ni borra pedidos, solo los consulta y cambia su estado (update)
    Route::resource('pedidos', PedidoController::class)->only(['index', 'show', 'update']);
});

require __DIR__.'/settings.php';
