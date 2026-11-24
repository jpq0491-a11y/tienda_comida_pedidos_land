<nav class="navbar navbar-expand-lg navbar-tienda border-bottom sticky-top">
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
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('menu') ? 'active' : '' }}"
                        @if (request()->routeIs('menu')) aria-current="page" @endif
                        href="{{ route('menu') }}">Menú</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                {{-- Contador: suma las cantidades del carrito en la sesión ([id => cantidad]) --}}
                @php($piezasEnCarrito = array_sum(session('carrito', [])))
                <a class="btn btn-tienda btn-sm" href="{{ route('carrito.index') }}">
                    <i class="bi bi-cart3"></i> Carrito
                    @if ($piezasEnCarrito > 0)
                        <span class="badge rounded-pill bg-light text-dark ms-1">
                            {{ $piezasEnCarrito }}
                            <span class="visually-hidden">productos en el carrito</span>
                        </span>
                    @endif
                </a>

                {{-- Si el administrador navega la tienda, tiene un atajo de regreso al panel --}}
                @can('administrar')
                    <a class="btn btn-outline-tienda btn-sm" href="{{ route('admin.inicio') }}">
                        <i class="bi bi-speedometer2"></i> Panel
                    </a>
                @endcan
            </div>
        </div>
    </div>
</nav>
