{{--
    Tarjeta de un producto. Se usa en la landing (destacados) y en el menú.
    - producto: el modelo Producto
    - mostrarCategoria: en el menú no hace falta, porque la categoría ya es el título de la sección
--}}
@props([
    'producto',
    'mostrarCategoria' => true,
])

{{-- Si el producto no está disponible, la tarjeta se ve apagada (clase .agotado en tienda.css) --}}
<div class="card h-100 tarjeta-producto {{ $producto->disponible ? '' : 'agotado' }}">
    @unless ($producto->disponible)
        <span class="badge text-bg-secondary position-absolute top-0 end-0 m-2">Agotado</span>
    @endunless

    @if ($producto->imagen)
        <img class="card-img-top" src="{{ asset('storage/'.$producto->imagen) }}" alt="{{ $producto->nombre }}">
    @else
        {{-- Los productos del seeder no tienen imagen: se muestra un ícono --}}
        <div class="card-img-top sin-imagen d-flex align-items-center justify-content-center">
            <i class="bi bi-egg-fried display-4"></i>
        </div>
    @endif

    <div class="card-body p-4 text-center">
        @if ($mostrarCategoria)
            <span class="badge text-bg-warning mb-2">{{ $producto->categoria->nombre }}</span>
        @endif

        <h5 class="fw-bolder">{{ $producto->nombre }}</h5>
        <p class="text-body-secondary small mb-2">{{ $producto->descripcion }}</p>

        {{-- Etiquetas (Vegetariano, Picante...) como insignias; solo si ya se cargaron con with() --}}
        @if ($producto->relationLoaded('etiquetas') && $producto->etiquetas->isNotEmpty())
            <div class="d-flex flex-wrap justify-content-center gap-1 mb-2">
                @foreach ($producto->etiquetas as $etiqueta)
                    <span class="badge rounded-pill text-bg-light border">{{ $etiqueta->descripcion }}</span>
                @endforeach
            </div>
        @endif

        <span class="fs-5 precio">${{ number_format($producto->precio, 2) }}</span>
    </div>

    {{-- Solo se puede agregar al carrito si está disponible --}}
    @if ($producto->disponible)
        <div class="card-footer bg-transparent border-top-0 p-4 pt-0">
            <form action="{{ route('carrito.agregar', $producto) }}" method="POST"
                class="d-flex justify-content-center gap-2">
                @csrf
                <label class="visually-hidden" for="cantidad-{{ $producto->id }}">Cantidad</label>
                {{-- Validación HTML5 en el navegador; el servidor vuelve a validar con CarritoRequest --}}
                <input type="number" class="form-control text-center" style="width: 4.5rem;"
                    id="cantidad-{{ $producto->id }}" name="cantidad" value="1"
                    min="1" max="{{ config('tienda.maximo_por_producto') }}" required>
                <button type="submit" class="btn btn-tienda">
                    <i class="bi bi-cart-plus"></i> Agregar
                </button>
            </form>
        </div>
    @endif
</div>
