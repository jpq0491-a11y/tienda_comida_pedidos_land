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
        $productos = $this->productosDisponibles($carrito);

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

    /**
     * Total del carrito SIN modificar la sesión (a diferencia de lineas(), no quita los agotados
     * ni pone avisos). Lo usa StorePedidoRequest para validar "¿con cuánto pagas?": si quitara
     * los agotados ahí, el controlador ya no podría darse cuenta de que algo se agotó.
     */
    public function total(): float
    {
        $carrito = session('carrito', []);

        $total = $this->productosDisponibles($carrito)
            ->sum(fn (Producto $producto) => $producto->precio * $carrito[$producto->id]);

        // round: con decimales, 179.8 puede quedar como 179.80000000001 y fallaría la regla min
        return round($total, 2);
    }

    /**
     * Productos del carrito que siguen disponibles (los borrados con SoftDeletes tampoco llegan).
     */
    private function productosDisponibles(array $carrito): Collection
    {
        return Producto::whereIn('id', array_keys($carrito))
            ->where('disponible', true)
            ->orderBy('nombre')
            ->get();
    }
}
