@props(['titulo' => 'Panel de administración'])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} | {{ config('app.name') }}</title>

    {{-- Bootstrap e íconos descargados en public/ (no se usa @vite aquí) --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.inicio') }}">
                <i class="bi bi-shop"></i> {{ config('app.name') }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuAdmin"
                aria-controls="menuAdmin" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuAdmin">
                {{-- Los enlaces del panel solo se muestran a quien pasa el Gate 'administrar' --}}
                @can('administrar')
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.inicio') ? 'active' : '' }}"
                                href="{{ route('admin.inicio') }}">
                                <i class="bi bi-house"></i> Inicio
                            </a>
                        </li>
                        {{-- Aquí se agregarán Categorías, Productos y Pedidos --}}
                    </ul>
                @endcan

                <div class="d-flex align-items-center gap-3 ms-auto">
                    <span class="navbar-text">
                        <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        {{-- Mensaje de éxito después de guardar, editar o borrar --}}
        @if (session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        {{ $slot }}
    </main>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
