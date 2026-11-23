<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
    <div class="container px-4 px-lg-5">
        <a class="navbar-brand fw-bolder" href="{{ route('home') }}">
            <i class="bi bi-shop"></i> {{ config('app.name') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuTienda"
            aria-controls="menuTienda" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuTienda">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                        @if (request()->routeIs('home')) aria-current="page" @endif
                        href="{{ route('home') }}">Inicio</a>
                </li>
                {{-- Aquí se agregará "Menú" en la Etapa 4 --}}
            </ul>

            <div class="d-flex align-items-center gap-2">
                {{-- Aquí irá el botón del carrito con su contador (Etapa 4) --}}

                {{-- Si el administrador navega la tienda, tiene un atajo de regreso al panel --}}
                @can('administrar')
                    <a class="btn btn-outline-dark btn-sm" href="{{ route('admin.inicio') }}">
                        <i class="bi bi-speedometer2"></i> Panel
                    </a>
                @endcan
            </div>
        </div>
    </div>
</nav>
