<x-layout-admin titulo="Editar categoría">
    <x-titulo-pagina titulo="Editar categoría" />

    <div class="card">
        <div class="card-body">
            <x-errores-formulario />

            <form action="{{ route('admin.categorias.update', $categoria) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.categorias._formulario')

                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('admin.categorias.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
</x-layout-admin>
