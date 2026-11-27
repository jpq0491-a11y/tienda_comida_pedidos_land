<x-layout-admin titulo="Pedidos">
    <x-titulo-pagina titulo="Pedidos" />

    {{-- Pendientes: pendiente, preparando y en camino --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0"><i class="bi bi-hourglass-split"></i> Por atender</h2>
            <span class="badge text-bg-warning">{{ $pendientes->count() }}</span>
        </div>
        @include('admin.pedidos._tabla', ['pedidos' => $pendientes, 'vacio' => 'No hay pedidos por atender.'])
    </div>

    {{-- Realizados: entregados y cancelados, paginados --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0"><i class="bi bi-check2-all"></i> Realizados</h2>
            {{-- total() es el número de todos los realizados, no solo los de esta página --}}
            <span class="badge text-bg-secondary">{{ $realizados->total() }}</span>
        </div>
        @include('admin.pedidos._tabla', ['pedidos' => $realizados, 'vacio' => 'Todavía no hay pedidos realizados.'])
    </div>

    <div class="mt-3">
        {{ $realizados->links() }}
    </div>
</x-layout-admin>
