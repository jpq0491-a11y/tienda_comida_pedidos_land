<x-layout-admin titulo="Editar producto">
    <x-titulo-pagina titulo="Editar producto" />

    <div class="card">
        <div class="card-body">
            <x-errores-formulario />

            <form action="{{ route('admin.productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
                {{-- enctype es obligatorio para mandar archivos; sin él solo llega el nombre --}}
                @csrf
                @method('PUT')
                @include('admin.productos._formulario')

                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('admin.productos.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
</x-layout-admin>
