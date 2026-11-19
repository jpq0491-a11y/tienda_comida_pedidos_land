<x-layout-admin titulo="Categorías">
    <x-titulo-pagina titulo="Categorías">
        <a href="{{ route('admin.categorias.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nueva categoría
        </a>
    </x-titulo-pagina>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Orden</th>
                        <th>Nombre</th>
                        <th>Productos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->orden }}</td>
                            <td>{{ $categoria->nombre }}</td>
                            {{-- productos_count viene del withCount del controlador --}}
                            <td>{{ $categoria->productos_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.categorias.edit', $categoria) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <form action="{{ route('admin.categorias.destroy', $categoria) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Seguro que quieres borrar esta categoría?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Borrar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Todavía no hay categorías.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout-admin>
