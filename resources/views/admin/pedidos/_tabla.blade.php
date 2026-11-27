{{-- Tabla de pedidos; se usa para los pendientes y para los realizados. Recibe $pedidos y $vacio --}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Sucursal</th>
                <th>Entrega</th>
                <th class="text-center">Productos</th>
                <th class="text-end">Total</th>
                <th class="text-center">Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pedidos as $pedido)
                <tr>
                    <td class="fw-semibold">
                        <a href="{{ route('admin.pedidos.show', $pedido) }}">{{ $pedido->folio }}</a>
                    </td>
                    <td class="text-nowrap">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $pedido->nombre_cliente }}</td>
                    {{-- La sucursal ya viene cargada por el with('sucursal') del controlador --}}
                    <td>{{ $pedido->sucursal->nombre }}</td>
                    <td>
                        @if ($pedido->tipo_entrega === 'domicilio')
                            <i class="bi bi-scooter"></i> Domicilio
                        @else
                            <i class="bi bi-bag"></i> Recoger
                        @endif
                    </td>
                    {{-- detalles_count lo agrega el withCount('detalles'): no hace otra consulta --}}
                    <td class="text-center">{{ $pedido->detalles_count }}</td>
                    <td class="text-end">${{ number_format($pedido->total, 2) }}</td>
                    <td class="text-center"><x-estado-pedido :estado="$pedido->estado" /></td>
                    <td class="text-end">
                        <a href="{{ route('admin.pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-primary text-nowrap">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">{{ $vacio }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
