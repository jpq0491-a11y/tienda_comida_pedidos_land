<x-layout-admin :titulo="$producto->nombre">
    <x-titulo-pagina :titulo="$producto->nombre">
        <a href="{{ route('admin.productos.edit', $producto) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
    </x-titulo-pagina>

    <div class="card">
        <div class="row g-0">
            <div class="col-md-4">
                @if ($producto->imagen)
                    <img src="{{ Storage::url($producto->imagen) }}" alt="{{ $producto->nombre }}"
                        class="img-fluid rounded-start w-100 h-100" style="object-fit: cover; max-height: 320px;">
                @else
                    <div class="bg-light text-muted d-flex flex-column align-items-center justify-content-center h-100 py-5 rounded-start">
                        <i class="bi bi-image display-4"></i>
                        <span>Sin imagen</span>
                    </div>
                @endif
            </div>

            <div class="col-md-8">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Categoría</dt>
                        <dd class="col-sm-8">{{ $producto->categoria->nombre }}</dd>

                        <dt class="col-sm-4">Descripción</dt>
                        <dd class="col-sm-8">{{ $producto->descripcion ?: '—' }}</dd>

                        <dt class="col-sm-4">Precio</dt>
                        <dd class="col-sm-8">${{ number_format($producto->precio, 2) }}</dd>

                        <dt class="col-sm-4">Disponible</dt>
                        <dd class="col-sm-8">
                            @if ($producto->disponible)
                                <span class="badge text-bg-success">Sí</span>
                            @else
                                <span class="badge text-bg-secondary">No (aparece como agotado en el menú)</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Etiquetas</dt>
                        <dd class="col-sm-8">
                            @forelse ($producto->etiquetas as $etiqueta)
                                <span class="badge text-bg-info">{{ $etiqueta->descripcion }}</span>
                            @empty
                                <span class="text-muted">Sin etiquetas</span>
                            @endforelse
                        </dd>

                        <dt class="col-sm-4">Creado</dt>
                        <dd class="col-sm-8">{{ $producto->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Última actualización</dt>
                        <dd class="col-sm-8 mb-0">{{ $producto->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <a href="{{ route('admin.productos.index') }}" class="btn btn-link px-0">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
        <form action="{{ route('admin.productos.destroy', $producto) }}" method="POST" class="ms-auto"
            onsubmit="return confirm('¿Seguro que quieres borrar este producto?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-trash"></i> Borrar
            </button>
        </form>
    </div>
</x-layout-admin>
