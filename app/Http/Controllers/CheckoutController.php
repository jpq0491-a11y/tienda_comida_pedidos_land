<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Services\Carrito;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Checkout: el cliente revisa su pedido y escribe sus datos para confirmarlo.
 */
class CheckoutController extends Controller
{
    public function create(Carrito $carrito): View|RedirectResponse
    {
        $lineas = $carrito->lineas();

        // Sin productos no hay nada que pedir
        if ($lineas->isEmpty()) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío. Agrega platillos antes de hacer tu pedido.');
        }

        return view('tienda.checkout', [
            'lineas' => $lineas,
            'total' => $lineas->sum('subtotal'),
            'sucursal' => Sucursal::first(),
        ]);
    }
}
