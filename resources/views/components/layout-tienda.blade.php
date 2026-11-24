@props([
    'titulo' => null,
    'descripcion' => 'Comida casera para recoger en sucursal o con entrega a domicilio.',
])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $descripcion }}">
    {{-- En la landing no se pasa título y solo se muestra el nombre del negocio --}}
    <title>{{ $titulo ? $titulo.' | ' : '' }}{{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Mismo Bootstrap local que el panel admin (no se usa @vite aquí) --}}
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    {{-- Estilos propios: van después de Bootstrap para poder sobrescribirlo --}}
    <link rel="stylesheet" href="{{ asset('css/tienda.css') }}">
</head>
{{-- d-flex + flex-column + min-vh-100 mantienen el footer abajo aunque haya poco contenido --}}
<body class="d-flex flex-column min-vh-100">
    <x-navbar-tienda />


    <main class="flex-grow-1">
        {{-- Mensajes flash y errores de validación (carrito y checkout) --}}
        @if (session('exito') || session('error') || $errors->any())
            <div class="container pt-4">
                <x-errores-formulario />
                @if (session('exito'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('exito') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            </div>
        @endif

        {{ $slot }}
    </main>

    <x-footer-tienda />

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
