<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Collection;

/**
 * Lee el carrito de la sesión y arma sus líneas con los datos actuales de la base de datos.
 * Lo usan el carrito y el checkout, así el cálculo está en un solo lugar.
 *
 * En la sesión solo se guarda [id del producto => cantidad], por ejemplo [3 => 2, 7 => 1].
 * El precio NO se guarda: siempre se lee de la base de datos, así el cliente no puede
 * modificarlo y si el admin cambia un precio, el carrito ya muestra el nuevo.
 */
class Carrito
{
    /**
     * Una línea por producto: ['producto' => Producto, 'cantidad' => int, 'subtotal' => float].
     * Si algún producto se agotó o se borró desde que se agregó, se quita del carrito y se avisa.
     */
    public function lineas(): Collection
    {
        $carrito = session('carrito', []);

        // Solo los productos que siguen disponibles (los borrados con SoftDeletes tampoco llegan)
        $productos = Producto::whereIn('id', array_keys($carrito))
            ->where('disponible', true)
            ->orderBy('nombre')
            ->get();

        if ($productos->count() < count($carrito)) {
            session(['carrito' => array_intersect_key($carrito, $productos->keyBy('id')->all())]);
            // now(): el aviso se muestra en esta misma página, no en la siguiente
            session()->now('error', 'Algunos productos ya no están disponibles y se quitaron de tu carrito.');
        }

        return $productos->map(fn (Producto $producto) => [
            'producto' => $producto,
            'cantidad' => $carrito[$producto->id],
            'subtotal' => $producto->precio * $carrito[$producto->id],
        ]);
    }
}
