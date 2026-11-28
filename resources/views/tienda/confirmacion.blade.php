<x-layout-tienda titulo="Pedido {{ $pedido->folio }}">
    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <div class="text-center mb-4">
                <i class="bi bi-bag-check-fill display-4 text-tienda"></i>
                <h1 class="fw-bolder mt-2 mb-1">Pedido {{ $pedido->folio }}</h1>
                <p class="text-body-secondary mb-0">Hecho el {{ $pedido->created_at->format('d/m/Y \a \l\a\s H:i') }}</p>
            </div>

            <div class="row g-4 justify-content-center">
                {{-- Entrega y pago --}}
                <div class="col-lg-5">
                    <div class="bg-white rounded-3 p-4 h-100">
                        <h5 class="fw-bolder mb-3">Entrega y pago</h5>
                        {{-- Estado guardado en la base de datos; lo cambia el admin desde el panel --}}
                        <p class="mb-3">
                            Estado: <x-estado-pedido :estado="$pedido->estado" />
                        </p>
                        {{-- El teléfono no se muestra: cualquiera con el enlace podría verlo --}}
                        <p class="mb-3">
                            <i class="bi bi-person text-tienda me-1"></i>
                            A nombre de <strong>{{ $pedido->nombre_cliente }}</strong>
                        </p>
                        @if ($pedido->tipo_entrega === 'domicilio')
                            <p class="mb-3">
                                <i class="bi bi-house-door text-tienda me-1"></i>
                                <strong>Entrega a domicilio</strong>
                                <br><span class="text-body-secondary small">{{ $pedido->direccion_completa }}</span>
                                @if ($pedido->referencia)
                                    <br><span class="text-body-secondary small">Referencia: {{ $pedido->referencia }}</span>
                                @endif
                            </p>
                            <p class="mb-3">
                                <i class="bi bi-cash-coin text-tienda me-1"></i>
                                <strong>Pago en efectivo</strong> al recibir tu pedido.
                                @if ($pedido->cambio !== null)
                                    <br><span class="text-body-secondary small">
                                        Pagas con ${{ number_format($pedido->paga_con, 2) }}, tu cambio: ${{ number_format($pedido->cambio, 2) }}
                                    </span>
                                @else
                                    <br><span class="text-body-secondary small">Pagas con el monto exacto.</span>
                                @endif
                            </p>
                            <p class="mb-0 text-body-secondary small">
                                ¿Dudas? Llama a {{ $pedido->sucursal->nombre }}: {{ $pedido->sucursal->telefono }}
                            </p>
                        @else
                            <p class="mb-3">
                                <i class="bi bi-shop text-tienda me-1"></i>
                                <strong>Recoger en sucursal</strong>
                                <br><span class="text-body-secondary small">{{ $pedido->sucursal->nombre }}: {{ $pedido->sucursal->direccion }}</span>
                                <br><span class="text-body-secondary small">Tel. {{ $pedido->sucursal->telefono }}</span>
                            </p>
                            <p class="mb-0">
                                <i class="bi bi-cash-coin text-tienda me-1"></i>
                                <strong>Pago en efectivo</strong> al recoger tu pedido.
                            </p>
                        @endif
                        @if ($pedido->notas)
                            <p class="mt-3 mb-0">
                                <i class="bi bi-chat-left-text text-tienda me-1"></i>
                                <strong>Notas:</strong> {{ $pedido->notas }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Productos: cantidad y precio salen de la tabla pivote (lo que se cobró, no el precio actual) --}}
                <div class="col-lg-5">
                    <div class="bg-white rounded-3 p-4 h-100">
                        <h5 class="fw-bolder mb-3">Tu pedido</h5>
                        <ul class="list-unstyled mb-0">
                            @foreach ($pedido->productos as $producto)
                                <li class="d-flex justify-content-between gap-2 py-2 border-bottom">
                                    <span>
                                        {{ $producto->pivot->cantidad }} × {{ $producto->nombre }}
                                        <br><span class="text-body-secondary small">${{ number_format($producto->pivot->precio_unitario, 2) }} c/u</span>
                                    </span>
                                    <span class="text-nowrap">${{ number_format($producto->pivot->subtotal, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="d-flex justify-content-between fs-5 mt-3">
                            <span>Total</span>
                            <span class="precio">${{ number_format($pedido->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a class="btn btn-tienda" href="{{ route('menu') }}">Volver al menú</a>
            </div>
        </div>
    </section>
</x-layout-tienda>
