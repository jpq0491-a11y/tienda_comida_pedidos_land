<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index()
    {
        // withCount agrega 'productos_count' con una sola consulta (sin N+1)
        $categorias = Categoria::withCount('productos')->orderBy('orden')->get();

        return view('admin.categorias.index', compact('categorias'));
    }

    public function create()
    {
        // Categoría vacía para el formulario; se propone el siguiente número de orden
        $categoria = new Categoria(['orden' => Categoria::max('orden') + 1]);

        return view('admin.categorias.crear', compact('categoria'));
    }

    public function store(StoreCategoriaRequest $request)
    {
        Categoria::create($request->validated());

        return redirect()->route('admin.categorias.index')
            ->with('exito', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        return view('admin.categorias.editar', compact('categoria'));
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());

        return redirect()->route('admin.categorias.index')
            ->with('exito', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        // La base de datos no deja borrar una categoría con productos (restrictOnDelete).
        // Se cuentan también los productos borrados con soft delete, porque siguen en la tabla.
        if ($categoria->productos()->withTrashed()->exists()) {
            return redirect()->route('admin.categorias.index')
                ->with('error', 'No se puede borrar "'.$categoria->nombre.'" porque tiene productos.');
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')
            ->with('exito', 'Categoría eliminada correctamente.');
    }
}
