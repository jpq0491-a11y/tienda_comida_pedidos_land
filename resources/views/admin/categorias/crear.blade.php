<x-layout-admin titulo="Nueva categoría">
    <x-titulo-pagina titulo="Nueva categoría" />

    <div class="card">
        <div class="card-body">
            <x-errores-formulario />

            <form action="{{ route('admin.categorias.store') }}" method="POST">
                @csrf
                @include('admin.categorias._formulario')

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('admin.categorias.index') }}" class="btn btn-link">Cancelar</a>
            </form>
        </div>
    </div>
</x-layout-admin>
