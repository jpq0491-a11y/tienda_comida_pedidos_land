<x-layout-admin titulo="Nuevo producto">
    <x-titulo-pagina titulo="Nuevo producto" />

    <div class="card">
        <div class="card-body">
            <x-errores-formulario />

            <form action="{{ route('admin.productos.store') }}" method="POST" enctype="multipart/form-data">
                {{-- enctype es obligatorio para mandar archivos; sin él solo llega el nombre --}}
                @csrf
                @include('admin.productos._formulario')

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('admin.productos.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
</x-layout-admin>
