<x-layout-tienda>
    {{-- Encabezado principal (hero) --}}
    <header class="hero-tienda py-5">
        <div class="container px-4 px-lg-5 my-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Comida casera, <span>hecha al momento</span></h1>
                <p class="lead fw-normal text-white-50 mb-4">
                    Pide en línea y recoge en sucursal o recíbelo en tu casa.
                </p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <a class="btn btn-tienda btn-lg px-4" href="{{ route('menu') }}">Ver menú</a>
                    <a class="btn btn-outline-light btn-lg px-4" href="#como-pedir">¿Cómo pedir?</a>
                </div>
            </div>
        </div>
    </header>

    {{-- Pasos para hacer un pedido --}}
    <section id="como-pedir" class="py-5">
        <div class="container px-4 px-lg-5">
            <h2 class="fw-bolder text-center mb-5">¿Cómo pedir?</h2>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <span class="icono-paso"><i class="bi bi-basket"></i></span>
                    <h5 class="fw-bolder mt-3">1. Elige tus platillos</h5>
                    <p class="text-body-secondary mb-0">Agrégalos al carrito, no necesitas crear una cuenta.</p>
                </div>
                <div class="col-md-4">
                    <span class="icono-paso"><i class="bi bi-bicycle"></i></span>
                    <h5 class="fw-bolder mt-3">2. Recoge o pide a domicilio</h5>
                    <p class="text-body-secondary mb-0">
                        Entregamos hasta {{ $sucursal ? (float) $sucursal->radio_entrega_km : 3 }} km de la sucursal.
                    </p>
                </div>
                <div class="col-md-4">
                    <span class="icono-paso"><i class="bi bi-cash-coin"></i></span>
                    <h5 class="fw-bolder mt-3">3. Paga en efectivo</h5>
                    <p class="text-body-secondary mb-0">Dinos con cuánto pagas y llevamos tu cambio.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Productos destacados: si el menú está vacío no se muestra la sección --}}
    @if ($destacados->isNotEmpty())
        <section id="destacados" class="py-5 bg-white">
            <div class="container px-4 px-lg-5">
                <h2 class="fw-bolder text-center mb-5">Algunos de nuestros platillos</h2>
                <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-sm-2 row-cols-xl-4 justify-content-center">
                    @foreach ($destacados as $producto)
                        <div class="col mb-5">
                            <x-tarjeta-producto :producto="$producto" />
                        </div>
                    @endforeach
                </div>
                <div class="text-center">
                    <a class="btn btn-outline-tienda" href="{{ route('menu') }}">Ver menú completo</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Ubicación y horario --}}
    <section id="ubicacion" class="py-5">
        <div class="container px-4 px-lg-5">
            <h2 class="fw-bolder text-center mb-5">Visítanos</h2>
            <div class="row g-4 justify-content-center">
                @if ($sucursal)
                    <div class="col-md-5">
                        <h5 class="fw-bolder">{{ $sucursal->nombre }}</h5>
                        <p class="mb-1"><i class="bi bi-geo-alt me-1"></i> {{ $sucursal->direccion }}</p>
                        <p class="mb-3"><i class="bi bi-telephone me-1"></i> {{ $sucursal->telefono }}</p>
                        {{-- Abre Google Maps con las coordenadas de la sucursal --}}
                        <a class="btn btn-outline-tienda" target="_blank" rel="noopener"
                            href="https://www.google.com/maps/search/?api=1&query={{ $sucursal->latitud }},{{ $sucursal->longitud }}">
                            <i class="bi bi-map"></i> Cómo llegar
                        </a>
                    </div>
                @endif
                <div class="col-md-5">
                    <h5 class="fw-bolder">Horario</h5>
                    <ul class="list-unstyled mb-0">
                        @foreach ($horario as $dias => $horas)
                            <li><strong>{{ $dias }}:</strong> {{ $horas }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>
</x-layout-tienda>
