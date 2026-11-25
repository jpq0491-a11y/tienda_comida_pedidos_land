<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePedidoRequest;
use App\Mail\PedidoNuevo;
use App\Models\Pedido;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\CalculadoraDistancia;
use App\Services\Carrito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

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
            'total' => round($lineas->sum('subtotal'), 2),
            'sucursal' => Sucursal::first(),
        ]);
    }

    public function store(StorePedidoRequest $request, Carrito $carrito, CalculadoraDistancia $calculadora): RedirectResponse
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

        $sucursal = Sucursal::firstOrFail();
        $datos = $request->validated();

        // La distancia la calcula el servidor con las coordenadas del mapa: la que muestra el JS
        // es solo un aviso y se podría alterar. Con "recoger" no hay coordenadas (queda null).
        $distanciaKm = null;
        if ($datos['tipo_entrega'] === 'domicilio') {
            $distanciaKm = round($calculadora->kilometros(
                $sucursal->latitud, $sucursal->longitud, $datos['latitud'], $datos['longitud']
            ), 2);
        }

        // DB::transaction no es una consulta: agrupa el pedido y sus productos para que se guarden
        // todos o ninguno. Si algo falla a la mitad, no queda un pedido sin productos.
        $pedido = DB::transaction(function () use ($datos, $lineas, $sucursal, $distanciaKm) {
            // Datos del formulario (ya validados, incluye tipo_entrega, y la dirección, las coordenadas
            // y paga_con solo si es a domicilio) + los que decide el servidor
            $pedido = Pedido::create(array_merge($datos, [
                'folio' => $this->generarFolio(),
                'user_id' => null, // en la Etapa 8 se liga a la cuenta del cliente si inició sesión
                'sucursal_id' => $sucursal->id,
                'distancia_km' => $distanciaKm,
                'metodo_pago' => 'efectivo',
                'total' => round($lineas->sum('subtotal'), 2),
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

        $this->avisarAdministradores($pedido);

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
     * Manda el correo de pedido nuevo a todos los usuarios con rol admin.
     * Si el correo falla, el pedido ya está guardado: se registra el error en el log
     * (storage/logs/laravel.log) y el cliente sigue a su confirmación sin ver nada raro.
     */
    private function avisarAdministradores(Pedido $pedido): void
    {
        $correos = User::where('rol', 'admin')->pluck('email');

        if ($correos->isEmpty()) {
            return;
        }

        try {
            Mail::to($correos)->send(new PedidoNuevo($pedido));
        } catch (Throwable $error) {
            report($error);
        }
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
