<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\View\View;

/**
 * Controlador invokable: muestra el menú público agrupado por categoría.
 */
class MenuController extends Controller
{
    public function __invoke(): View
    {
        $categorias = Categoria::orderBy('orden')
            // whereHas: deja fuera las categorías que no tienen ningún producto
            ->whereHas('productos')
            // with: carga los productos y sus etiquetas de una vez (evita el problema N+1).
            // Los agotados también se muestran, pero al final de su categoría
            ->with([
                'productos' => fn ($consulta) => $consulta->orderByDesc('disponible')->orderBy('nombre'),
                'productos.etiquetas',
            ])
            ->get();

        return view('tienda.menu', compact('categorias'));
    }
}
