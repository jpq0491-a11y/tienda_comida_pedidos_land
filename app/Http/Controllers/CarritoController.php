<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarritoRequest;
use App\Models\Producto;
use App\Services\Carrito;
use Illuminate\Http\RedirectResponse;
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
    // Laravel crea el objeto Carrito y lo pasa al método (inyección de dependencias)
    public function index(Carrito $carrito): View
    {
        // Las líneas (producto, cantidad, subtotal) las arma la clase App\Services\Carrito
        $lineas = $carrito->lineas();

        return view('tienda.carrito', [
            'lineas' => $lineas,
            'total' => $lineas->sum('subtotal'),
        ]);
    }

    /**
     * Agrega piezas de un producto. Si ya estaba en el carrito, se suman a las que tenía.
     */
    public function agregar(CarritoRequest $request, Producto $producto): RedirectResponse
    {
        // El botón no aparece en los agotados, pero alguien podría mandar el formulario a mano
        if (! $producto->disponible) {
            return back()->with('error', "{$producto->nombre} está agotado por ahora.");
        }

        $carrito = session('carrito', []);
        $maximo = config('tienda.maximo_por_producto');
        $cantidad = ($carrito[$producto->id] ?? 0) + $request->validated('cantidad');

        if ($cantidad > $maximo) {
            return back()->with('error', "Puedes pedir máximo {$maximo} piezas de {$producto->nombre}. Ya tienes {$carrito[$producto->id]} en tu carrito.");
        }

        $carrito[$producto->id] = $cantidad;
        session(['carrito' => $carrito]);

        return back()->with('exito', "Agregaste {$producto->nombre} a tu carrito.");
    }

    /**
     * Cambia la cantidad de un producto que ya está en el carrito (no se suma, se reemplaza).
     */
    public function actualizar(CarritoRequest $request, Producto $producto): RedirectResponse
    {
        $carrito = session('carrito', []);

        if (! isset($carrito[$producto->id])) {
            return redirect()->route('carrito.index')->with('error', 'Ese producto no está en tu carrito.');
        }

        $carrito[$producto->id] = $request->validated('cantidad');
        session(['carrito' => $carrito]);

        return redirect()->route('carrito.index')->with('exito', "Cantidad de {$producto->nombre} actualizada.");
    }

    /**
     * Quita un producto del carrito.
     */
    public function quitar(Producto $producto): RedirectResponse
    {
        $carrito = session('carrito', []);
        unset($carrito[$producto->id]);
        session(['carrito' => $carrito]);

        return redirect()->route('carrito.index')->with('exito', "Quitaste {$producto->nombre} de tu carrito.");
    }
}
