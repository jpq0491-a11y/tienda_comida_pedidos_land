<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\View\View;

/**
 * Carrito de invitado guardado en la sesión de Laravel (no hace falta cuenta).
 *
 * En la sesión solo se guarda [id del producto => cantidad], por ejemplo [3 => 2, 7 => 1].
 * El precio NO se guarda: siempre se lee de la base de datos, así el cliente no puede
 * modificarlo y si el admin cambia un precio, el carrito ya muestra el nuevo.
 */
class CarritoController extends Controller
{
    public function index(): View
    {
        $carrito = session('carrito', []);

        // Solo los productos que siguen disponibles (los borrados con SoftDeletes tampoco llegan)
        $productos = Producto::whereIn('id', array_keys($carrito))
            ->where('disponible', true)
            ->orderBy('nombre')
            ->get();

        // Si algún producto se agotó o se borró desde que se agregó, se quita del carrito
        if ($productos->count() < count($carrito)) {
            session(['carrito' => array_intersect_key($carrito, $productos->keyBy('id')->all())]);
            session()->now('error', 'Algunos productos ya no están disponibles y se quitaron de tu carrito.');
        }

        // Una línea por producto: el producto, cuántos lleva y cuánto cuestan
        $lineas = $productos->map(fn (Producto $producto) => [
            'producto' => $producto,
            'cantidad' => $carrito[$producto->id],
            'subtotal' => $producto->precio * $carrito[$producto->id],
        ]);

        return view('tienda.carrito', [
            'lineas' => $lineas,
            'total' => $lineas->sum('subtotal'),
        ]);
    }
}
