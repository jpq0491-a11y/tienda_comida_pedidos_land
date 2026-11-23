<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\View\View;

/**
 * Controlador de una sola acción (invokable): solo muestra la landing.
 * En la ruta se registra sin nombre de método: Route::get('/', InicioController::class).
 */
class InicioController extends Controller
{
    public function __invoke(): View
    {
        return view('tienda.inicio', [
            'sucursal' => Sucursal::first(),
            // with('categoria') evita una consulta extra por cada producto (problema N+1)
            'destacados' => Producto::with('categoria')
                ->where('disponible', true)
                ->latest()
                ->take(4)
                ->get(),
            'horario' => config('tienda.horario', []),
        ]);
    }
}
