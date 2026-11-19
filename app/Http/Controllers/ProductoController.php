<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function index()
    {
        // with('categoria') trae todas las categorías en una sola consulta (sin N+1)
        $productos = Producto::with('categoria')->orderBy('categoria_id')->orderBy('nombre')->get();

        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        // Producto vacío para el formulario; por defecto aparece como disponible
        $producto = new Producto(['disponible' => true]);
        $categorias = Categoria::orderBy('orden')->get();

        return view('admin.productos.crear', compact('producto', 'categorias'));
    }

    public function store(StoreProductoRequest $request)
    {
        Producto::create($request->validated());

        return redirect()->route('admin.productos.index')
            ->with('exito', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('orden')->get();

        return view('admin.productos.editar', compact('producto', 'categorias'));
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $producto->update($request->validated());

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
