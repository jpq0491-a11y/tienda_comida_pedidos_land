<x-layout-tienda titulo="Confirmar pedido">
    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <h1 class="fw-bolder mb-4"><i class="bi bi-bag-check text-tienda"></i> Confirmar pedido</h1>

            {{-- data-total: el JS lo usa para calcular el cambio en vivo (el servidor lo vuelve a validar).
                 data-con-errores: si el servidor regresó el formulario, el JS no pisa lo que eligió --}}
            <form id="formulario-checkout" action="{{ route('checkout.store') }}" method="POST"
                data-total="{{ $total }}" @if (session()->hasOldInput()) data-con-errores @endif>
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

                        @php($esDomicilio = old('tipo_entrega') === 'domicilio')
                        <div class="bg-white rounded-3 p-4">
                            <h5 class="fw-bolder mb-3">Entrega y pago</h5>

                            <fieldset class="mb-3">
                                <legend class="form-label fs-6">¿Cómo quieres recibir tu pedido?</legend>
                                <div class="form-check">
                                    <input class="form-check-input @error('tipo_entrega') is-invalid @enderror" type="radio"
                                        name="tipo_entrega" id="entrega-recoger" value="recoger" @checked(! $esDomicilio)>
                                    <label class="form-check-label" for="entrega-recoger">
                                        <i class="bi bi-shop text-tienda me-1"></i><strong>Recoger en sucursal</strong>
                                        @if ($sucursal)
                                            <br><span class="text-body-secondary small">{{ $sucursal->nombre }}: {{ $sucursal->direccion }}</span>
                                        @endif
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input @error('tipo_entrega') is-invalid @enderror" type="radio"
                                        name="tipo_entrega" id="entrega-domicilio" value="domicilio" @checked($esDomicilio)>
                                    <label class="form-check-label" for="entrega-domicilio">
                                        <i class="bi bi-house-door text-tienda me-1"></i><strong>Entrega a domicilio</strong>
                                    </label>
                                    @error('tipo_entrega')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>

                            {{-- Solo con domicilio; el JS lo muestra u oculta según la opción elegida --}}
                            <div id="datos-domicilio" @class(['d-none' => ! $esDomicilio])>
                                {{-- data-requerido: el JS les pone required solo cuando se elige domicilio --}}
                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <label for="calle" class="form-label">Calle</label>
                                        <input type="text" class="form-control @error('calle') is-invalid @enderror"
                                            id="calle" name="calle" value="{{ old('calle') }}" maxlength="150"
                                            autocomplete="address-line1" data-requerido @required($esDomicilio)>
                                        @error('calle')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
                                        <label for="numero_exterior" class="form-label">Número exterior</label>
                                        <input type="text" class="form-control @error('numero_exterior') is-invalid @enderror"
                                            id="numero_exterior" name="numero_exterior" value="{{ old('numero_exterior') }}"
                                            maxlength="20" placeholder="120 o S/N" data-requerido @required($esDomicilio)>
                                        @error('numero_exterior')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6">
                                        <label for="numero_interior" class="form-label">Número interior <span class="text-body-secondary">(opcional)</span></label>
                                        <input type="text" class="form-control @error('numero_interior') is-invalid @enderror"
                                            id="numero_interior" name="numero_interior" value="{{ old('numero_interior') }}"
                                            maxlength="20" placeholder="Depto. 4">
                                        @error('numero_interior')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="colonia" class="form-label">Colonia</label>
                                        <input type="text" class="form-control @error('colonia') is-invalid @enderror"
                                            id="colonia" name="colonia" value="{{ old('colonia') }}" maxlength="100"
                                            data-requerido @required($esDomicilio)>
                                        @error('colonia')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="referencia" class="form-label">Referencia <span class="text-body-secondary">(opcional)</span></label>
                                        <input type="text" class="form-control @error('referencia') is-invalid @enderror"
                                            id="referencia" name="referencia" value="{{ old('referencia') }}" maxlength="255"
                                            placeholder="Por ejemplo: entre Hidalgo y Morelos, portón verde">
                                        @error('referencia')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Mapa: el cliente marca su casa y el JS llena latitud y longitud (inputs hidden) --}}
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <span class="form-label mb-0" id="etiqueta-mapa">Ubicación en el mapa</span>
                                        <button type="button" class="btn btn-sm btn-outline-tienda" id="usar-mi-ubicacion">
                                            <i class="bi bi-crosshair me-1"></i>Usar mi ubicación
                                        </button>
                                    </div>
                                    @if ($sucursal)
                                        <div id="mapa-entrega" class="mapa-entrega rounded-3" role="application" aria-labelledby="etiqueta-mapa"
                                            data-latitud="{{ $sucursal->latitud }}" data-longitud="{{ $sucursal->longitud }}"
                                            data-sucursal="{{ $sucursal->nombre }}" data-radio-km="{{ $sucursal->radio_entrega_km }}"></div>
                                    @endif
                                    <input type="hidden" id="latitud" name="latitud" value="{{ old('latitud') }}">
                                    <input type="hidden" id="longitud" name="longitud" value="{{ old('longitud') }}">
                                    <div class="form-text">
                                        Haz clic en el mapa donde entregamos (puedes arrastrar el marcador para ajustarlo).
                                        @if ($sucursal)
                                            El círculo es nuestra zona de entrega: hasta {{ $sucursal->radio_entrega_km }} km de la sucursal.
                                        @endif
                                    </div>
                                    <p id="aviso-ubicacion" class="small mt-1 mb-0" aria-live="polite"></p>
                                    {{-- Los dos campos son un solo dato para el cliente: se muestra un solo error --}}
                                    @if ($errors->hasAny(['latitud', 'longitud']))
                                        <div class="small text-danger mt-1">{{ $errors->first('latitud') ?: $errors->first('longitud') }}</div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label for="paga_con" class="form-label">¿Con cuánto pagas? <span class="text-body-secondary">(opcional)</span></label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('paga_con') is-invalid @enderror"
                                            id="paga_con" name="paga_con" value="{{ old('paga_con') }}"
                                            min="{{ $total }}" max="99999" step="0.01" inputmode="decimal"
                                            aria-describedby="ayuda-paga-con cambio">
                                        @error('paga_con')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div id="ayuda-paga-con" class="form-text">Para llevarte tu cambio. Déjalo vacío si pagas con el monto exacto.</div>
                                    <p id="cambio" class="small fw-semibold mt-1 mb-0" aria-live="polite"></p>
                                </div>
                            </div>

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

    {{-- Leaflet (mapas con OpenStreetMap, sin API key), descargado en public/vendor como Bootstrap --}}
    <x-slot:estilos>
        <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    </x-slot:estilos>

    {{-- Borrador del formulario, datos de domicilio, cálculo del cambio en vivo y mapa --}}
    <x-slot:scripts>
        <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
        <script src="{{ asset('js/checkout.js') }}?v={{ filemtime(public_path('js/checkout.js')) }}"></script>
    </x-slot:scripts>
</x-layout-tienda>
