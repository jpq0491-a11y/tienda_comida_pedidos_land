<?php

namespace App\Http\Controllers;

use App\Models\Pedido;

// Pedidos del panel de administración (el cliente crea sus pedidos en CheckoutController)
class PedidoController extends Controller
{
    public function index()
    {
        // with('sucursal') trae las sucursales en una sola consulta (sin N+1) y
        // withCount('detalles') agrega detalles_count con un COUNT en la misma consulta del pedido.
        // Los pendientes van del más viejo al más nuevo: el primero en llegar es el primero en atenderse.
        $pendientes = Pedido::pendientes()
            ->with('sucursal')
            ->withCount('detalles')
            ->oldest()
            ->get();

        // Los realizados crecen sin límite: se paginan, del más reciente al más viejo
        $realizados = Pedido::realizados()
            ->with('sucursal')
            ->withCount('detalles')
            ->latest()
            ->paginate(15);

        return view('admin.pedidos.index', compact('pendientes', 'realizados'));
    }

    public function show(Pedido $pedido)
    {
        // load() trae la sucursal y los productos (con cantidad, precio y subtotal de la pivote)
        // del pedido que ya se buscó por la ruta
        $pedido->load(['sucursal', 'productos']);

        return view('admin.pedidos.detalle', compact('pedido'));
    }
}
