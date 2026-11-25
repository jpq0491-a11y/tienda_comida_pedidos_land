<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePedidoRequest;
use App\Models\Pedido;
use App\Models\Sucursal;
use App\Services\Carrito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function store(StorePedidoRequest $request, Carrito $carrito): RedirectResponse
    {
        $productosAntes = count(session('carrito', []));
        $lineas = $carrito->lineas();

        if ($lineas->isEmpty()) {
            return redirect()->route('carrito.index')
                ->with('error', 'Tu carrito está vacío. Agrega platillos antes de hacer tu pedido.');
        }

        // Si algo se agotó mientras llenaba el formulario, el total cambió: que lo revise antes de pedir
        if ($lineas->count() < $productosAntes) {
            return redirect()->route('checkout.create')->withInput()
                ->with('error', 'Algunos productos se agotaron y se quitaron de tu pedido. Revisa el total antes de confirmar.');
        }

        // DB::transaction no es una consulta: agrupa el pedido y sus productos para que se guarden
        // todos o ninguno. Si algo falla a la mitad, no queda un pedido sin productos.
        $pedido = DB::transaction(function () use ($request, $lineas) {
            // Datos del formulario (ya validados) + los que decide el servidor
            $pedido = Pedido::create(array_merge($request->validated(), [
                'folio' => $this->generarFolio(),
                'user_id' => null, // en la Etapa 8 se liga a la cuenta del cliente si inició sesión
                'sucursal_id' => Sucursal::firstOrFail()->id,
                'tipo_entrega' => 'recoger', // la entrega a domicilio llega en la Etapa 6
                'metodo_pago' => 'efectivo',
                'total' => $lineas->sum('subtotal'),
                'estado' => 'pendiente',
            ]));

            // attach llena la tabla pivote detalle_pedidos con los datos extra de cada producto.
            // El precio se copia: si mañana cambia, el pedido sigue mostrando lo que se cobró.
            foreach ($lineas as $linea) {
                $pedido->productos()->attach($linea['producto']->id, [
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['producto']->precio,
                    'subtotal' => $linea['subtotal'],
                ]);
            }

            return $pedido;
        });

        // El pedido ya está guardado: se vacía el carrito
        session()->forget('carrito');

        return redirect()->route('pedido.confirmacion', $pedido)
            ->with('exito', '¡Recibimos tu pedido! Guarda tu folio por si necesitas consultarlo.');
    }

    /**
     * Página de confirmación del pedido. La URL usa el folio ({pedido:folio}), no el id:
     * con el id cualquiera podría ver otros pedidos cambiando el número (/pedido/1, /pedido/2...),
     * el folio es aleatorio y no se puede adivinar.
     */
    public function confirmacion(Pedido $pedido): View
    {
        return view('tienda.confirmacion', ['pedido' => $pedido]);
    }

    /**
     * Folio corto y difícil de adivinar, por ejemplo SC-7K2M9QXA ("SC" de Sabor de Casa).
     * Se usará en la URL de la página de confirmación.
     */
    private function generarFolio(): string
    {
        do {
            $folio = 'SC-'.Str::upper(Str::random(8));
        } while (Pedido::where('folio', $folio)->exists());

        return $folio;
    }
}
