<x-layout-admin titulo="Productos">
    <x-titulo-pagina titulo="Productos">
        <a href="{{ route('admin.productos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nuevo producto
        </a>
    </x-titulo-pagina>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 64px;">Imagen</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Etiquetas</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Disponible</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productos as $producto)
                        <tr>
                            <td>
                                @if ($producto->imagen)
                                    <img src="{{ Storage::url($producto->imagen) }}" alt="{{ $producto->nombre }}"
                                        class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                @else
                                    <div class="rounded bg-light text-muted d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $producto->nombre }}</td>
                            {{-- La categoría ya viene cargada por el with('categoria') del controlador --}}
                            <td>{{ $producto->categoria->nombre }}</td>
                            {{-- Las etiquetas también vienen cargadas por el with() --}}
                            <td>
                                @forelse ($producto->etiquetas as $etiqueta)
                                    <span class="badge text-bg-info">{{ $etiqueta->descripcion }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td class="text-end">${{ number_format($producto->precio, 2) }}</td>
                            <td class="text-center">
                                @if ($producto->disponible)
                                    <span class="badge text-bg-success">Sí</span>
                                @else
                                    <span class="badge text-bg-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.productos.edit', $producto) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <form action="{{ route('admin.productos.destroy', $producto) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Seguro que quieres borrar este producto?')">
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
                            <td colspan="7" class="text-center text-muted py-4">Todavía no hay productos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout-admin>
