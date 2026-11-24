<x-layout-tienda titulo="Confirmar pedido">
    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <h1 class="fw-bolder mb-4"><i class="bi bi-bag-check text-tienda"></i> Confirmar pedido</h1>

            <form id="formulario-checkout" action="{{ route('checkout.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    {{-- Datos del cliente --}}
                    <div class="col-lg-7">
                        <div class="bg-white rounded-3 p-4 mb-4">
                            <h5 class="fw-bolder mb-3">Tus datos</h5>

                            <div class="mb-3">
                                <label for="nombre_cliente" class="form-label">Nombre</label>
                                <input type="text" class="form-control @error('nombre_cliente') is-invalid @enderror"
                                    id="nombre_cliente" name="nombre_cliente"
                                    value="{{ old('nombre_cliente') }}" maxlength="100" autocomplete="name" required>
                                @error('nombre_cliente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                {{-- pattern: exactamente 10 dígitos; el servidor lo vuelve a validar --}}
                                <input type="tel" class="form-control @error('telefono') is-invalid @enderror"
                                    id="telefono" name="telefono"
                                    value="{{ old('telefono') }}" pattern="[0-9]{10}" maxlength="10" inputmode="numeric"
                                    autocomplete="tel" aria-describedby="ayuda-telefono" required>
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="ayuda-telefono" class="form-text">10 dígitos, sin espacios. Te llamamos si hay algún problema con tu pedido.</div>
                            </div>

                            <div>
                                <label for="notas" class="form-label">Notas <span class="text-body-secondary">(opcional)</span></label>
                                <textarea class="form-control @error('notas') is-invalid @enderror" id="notas" name="notas"
                                    rows="3" maxlength="500"
                                    placeholder="Por ejemplo: sin cebolla, salsa aparte...">{{ old('notas') }}</textarea>
                                @error('notas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="bg-white rounded-3 p-4">
                            <h5 class="fw-bolder mb-3">Entrega y pago</h5>
                            {{-- La opción de entrega a domicilio se agrega en la Etapa 6 --}}
                            <p class="mb-2">
                                <i class="bi bi-shop text-tienda me-1"></i>
                                <strong>Recoger en sucursal</strong>
                                @if ($sucursal)
                                    <br><span class="text-body-secondary small">{{ $sucursal->nombre }}: {{ $sucursal->direccion }}</span>
                                @endif
                            </p>
                            {{-- En el paso 3 aquí se pregunta con cuánto paga para calcular el cambio --}}
                            <p class="mb-0">
                                <i class="bi bi-cash-coin text-tienda me-1"></i>
                                <strong>Pago en efectivo</strong> al recibir tu pedido.
                            </p>
                        </div>
                    </div>

                    {{-- Resumen del pedido --}}
                    <div class="col-lg-5">
                        <div class="bg-white rounded-3 p-4">
                            <h5 class="fw-bolder mb-3">Tu pedido</h5>
                            <ul class="list-unstyled mb-0">
                                @foreach ($lineas as $linea)
                                    <li class="d-flex justify-content-between gap-2 py-2 border-bottom">
                                        <span>{{ $linea['cantidad'] }} × {{ $linea['producto']->nombre }}</span>
                                        <span class="text-nowrap">${{ number_format($linea['subtotal'], 2) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="d-flex justify-content-between fs-5 my-3">
                                <span>Total</span>
                                <span class="precio">${{ number_format($total, 2) }}</span>
                            </div>

                            <button type="submit" class="btn btn-tienda w-100 mb-2">Confirmar pedido</button>
                            <a class="btn btn-outline-tienda w-100" href="{{ route('carrito.index') }}">Modificar carrito</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- Guarda lo que escribe el cliente para que no se pierda si sale a modificar el carrito --}}
    <x-slot:scripts>
        <script src="{{ asset('js/checkout.js') }}"></script>
    </x-slot:scripts>
</x-layout-tienda>
