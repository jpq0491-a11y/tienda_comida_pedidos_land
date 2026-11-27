<x-layout-admin titulo="Pedido {{ $pedido->folio }}">
    <x-titulo-pagina titulo="Pedido {{ $pedido->folio }}">
        <x-estado-pedido :estado="$pedido->estado" class="fs-6" />
    </x-titulo-pagina>

    <x-errores-formulario />

    {{-- Cambiar el estado: PATCH a admin.pedidos.update, lo valida UpdatePedidoRequest --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.pedidos.update', $pedido) }}" method="POST" class="row g-2 align-items-center">
                @csrf
                @method('PATCH')
                <div class="col-auto">
                    <label for="estado" class="col-form-label fw-semibold">Estado del pedido</label>
                </div>
                <div class="col-auto">
                    <select id="estado" name="estado" class="form-select @error('estado') is-invalid @enderror">
                        @foreach ([...App\Models\Pedido::ESTADOS_PENDIENTES, ...App\Models\Pedido::ESTADOS_REALIZADOS] as $estado)
                            {{-- old() conserva lo elegido si la validación falla --}}
                            <option value="{{ $estado }}" @selected(old('estado', $pedido->estado) === $estado)>
                                {{ ucfirst(str_replace('_', ' ', $estado)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Guardar estado
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- Cliente, entrega y pago --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><h2 class="h5 mb-0"><i class="bi bi-person"></i> Cliente y entrega</h2></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Fecha</dt>
                        <dd class="col-sm-8">{{ $pedido->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Cliente</dt>
                        <dd class="col-sm-8">{{ $pedido->nombre_cliente }}</dd>

                        {{-- El teléfono solo se ve en el panel (en la confirmación pública no aparece) --}}
                        <dt class="col-sm-4">Teléfono</dt>
                        <dd class="col-sm-8">
                            <a href="tel:{{ $pedido->telefono }}"><i class="bi bi-telephone"></i> {{ $pedido->telefono }}</a>
                        </dd>

                        <dt class="col-sm-4">Sucursal</dt>
                        <dd class="col-sm-8">{{ $pedido->sucursal->nombre }}</dd>

                        <dt class="col-sm-4">Entrega</dt>
                        @if ($pedido->tipo_entrega === 'domicilio')
                            <dd class="col-sm-8"><i class="bi bi-scooter"></i> A domicilio</dd>

                            <dt class="col-sm-4">Dirección</dt>
                            <dd class="col-sm-8">{{ $pedido->direccion_completa }}</dd>

                            <dt class="col-sm-4">Referencia</dt>
                            <dd class="col-sm-8">{{ $pedido->referencia ?: '—' }}</dd>

                            <dt class="col-sm-4">Ubicación</dt>
                            <dd class="col-sm-8">
                                {{-- Mismo enlace que el correo: abre el punto en Google Maps (o su app en el celular) --}}
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $pedido->latitud }},{{ $pedido->longitud }}"
                                    target="_blank" rel="noopener">
                                    <i class="bi bi-geo-alt"></i> Ver en Google Maps
                                </a>
                                <br><span class="text-muted small">{{ number_format($pedido->distancia_km, 1) }} km en línea recta desde la sucursal</span>
                            </dd>
                        @else
                            <dd class="col-sm-8"><i class="bi bi-bag"></i> Recoge en sucursal</dd>
                        @endif

                        <dt class="col-sm-4">Pago</dt>
                        <dd class="col-sm-8">
                            Efectivo
                            @if ($pedido->cambio !== null)
                                <br><span class="text-muted small">Paga con ${{ number_format($pedido->paga_con, 2) }}</span>
                                <br><strong>Llevar ${{ number_format($pedido->cambio, 2) }} de cambio</strong>
                            @elseif ($pedido->tipo_entrega === 'domicilio')
                                <br><span class="text-muted small">Paga con el monto exacto</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Notas</dt>
                        <dd class="col-sm-8 mb-0">{{ $pedido->notas ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Productos del pedido --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><h2 class="h5 mb-0"><i class="bi bi-basket"></i> Productos</h2></div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Los productos vienen del load('productos'); cantidad, precio y subtotal salen de la pivote --}}
                            @foreach ($pedido->productos as $producto)
                                <tr>
                                    <td>
                                        {{ $producto->nombre }}
                                        {{-- withTrashed() en la relación: el producto pudo haberse borrado después del pedido --}}
                                        @if ($producto->trashed())
                                            <span class="badge text-bg-secondary">Ya no está en el menú</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $producto->pivot->cantidad }}</td>
                                    <td class="text-end">${{ number_format($producto->pivot->precio_unitario, 2) }}</td>
                                    <td class="text-end">${{ number_format($producto->pivot->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end">${{ number_format($pedido->total, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('admin.pedidos.index') }}" class="btn btn-link px-0 mt-3">
        <i class="bi bi-arrow-left"></i> Volver a los pedidos
    </a>
</x-layout-admin>
