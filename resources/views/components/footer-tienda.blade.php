<footer class="bg-tienda-cafe text-white-50 pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="text-white fw-bolder">
                    <i class="bi bi-shop"></i> {{ config('app.name') }}
                </h5>
                <p class="mb-0">Comida casera hecha al momento. Recoge en sucursal o pide a domicilio.</p>
            </div>

            <div class="col-md-4">
                <h6 class="text-white text-uppercase">Visítanos</h6>
                {{-- Si todavía no se ha sembrado la sucursal, no se muestra esta parte --}}
                @if ($sucursal)
                    <ul class="list-unstyled mb-0">
                        <li><i class="bi bi-geo-alt me-1"></i> {{ $sucursal->direccion }}</li>
                        <li>
                            <i class="bi bi-telephone me-1"></i>
                            <a class="link-light link-underline-opacity-0" href="tel:{{ $sucursal->telefono }}">{{ $sucursal->telefono }}</a>
                        </li>
                    </ul>
                @endif
            </div>

            <div class="col-md-4">
                <h6 class="text-white text-uppercase">Horario</h6>
                <ul class="list-unstyled mb-0">
                    @foreach ($horario as $dias => $horas)
                        <li><span class="text-white">{{ $dias }}:</span> {{ $horas }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 small">
            <span>&copy; {{ now()->year }} {{ config('app.name') }}</span>
            {{-- El cliente compra como invitado; este enlace es para el administrador --}}
            @guest
                <a class="link-secondary link-underline-opacity-0" href="{{ route('login') }}">Acceso administrador</a>
            @endguest
        </div>
    </div>
</footer>
