<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function index()
    {
        // with() trae categorías y etiquetas en consultas aparte, no una por producto (sin N+1)
        $productos = Producto::with(['categoria', 'etiquetas'])->orderBy('categoria_id')->orderBy('nombre')->get();

        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        // Producto vacío para el formulario; por defecto aparece como disponible
        $producto = new Producto(['disponible' => true]);
        $categorias = Categoria::orderBy('orden')->get();
        $etiquetas = Etiqueta::orderBy('descripcion')->get();

        return view('admin.productos.crear', compact('producto', 'categorias', 'etiquetas'));
    }

    public function store(StoreProductoRequest $request)
    {
        // 'etiquetas' no es columna de productos: se guarda aparte en la tabla pivote
        $producto = Producto::create($request->safe()->except('etiquetas'));

        // attach agrega las filas en etiqueta_producto (el producto es nuevo, no tiene ninguna)
        $producto->etiquetas()->attach($request->validated('etiquetas', []));

        return redirect()->route('admin.productos.index')
            ->with('exito', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('orden')->get();
        $etiquetas = Etiqueta::orderBy('descripcion')->get();

        return view('admin.productos.editar', compact('producto', 'categorias', 'etiquetas'));
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $producto->update($request->safe()->except('etiquetas'));

        // sync deja exactamente las etiquetas elegidas: agrega las nuevas y quita las desmarcadas.
        // Si no se elige ninguna, el select no manda nada y [] quita todas.
        $producto->etiquetas()->sync($request->validated('etiquetas', []));

        return redirect()->route('admin.productos.index')
            ->with('exito', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        // Soft delete: el producto deja de verse, pero sigue en la tabla para los pedidos anteriores
        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('exito', 'Producto eliminado correctamente.');
    }
}
