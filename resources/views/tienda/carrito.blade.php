<x-layout-tienda titulo="Carrito">
    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <h1 class="fw-bolder mb-4"><i class="bi bi-cart3 text-tienda"></i> Tu carrito</h1>

            @if ($lineas->isEmpty())
                <div class="text-center bg-white rounded-3 p-5">
                    <i class="bi bi-cart-x display-4 text-tienda"></i>
                    <p class="lead mt-3">Tu carrito está vacío.</p>
                    <a class="btn btn-tienda" href="{{ route('menu') }}">Ver menú</a>
                </div>
            @else
                <div class="row g-4">
                    {{-- Productos del carrito --}}
                    <div class="col-lg-8">
                        <div class="table-responsive bg-white rounded-3 p-3">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Producto</th>
                                        <th scope="col" class="text-end">Precio</th>
                                        <th scope="col" class="text-center">Cantidad</th>
                                        <th scope="col" class="text-end">Subtotal</th>
                                        <th scope="col"><span class="visually-hidden">Quitar</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lineas as $linea)
                                        <tr>
                                            <td class="fw-bold">{{ $linea['producto']->nombre }}</td>
                                            <td class="text-end">${{ number_format($linea['producto']->precio, 2) }}</td>
                                            <td class="text-center">
                                                {{-- Cambiar cantidad: PATCH porque solo modifica un dato --}}
                                                <form action="{{ route('carrito.actualizar', $linea['producto']) }}" method="POST"
                                                    class="d-inline-flex gap-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <label class="visually-hidden" for="cantidad-{{ $linea['producto']->id }}">
                                                        Cantidad de {{ $linea['producto']->nombre }}
                                                    </label>
                                                    <input type="number" class="form-control form-control-sm text-center" style="width: 4.5rem;"
                                                        id="cantidad-{{ $linea['producto']->id }}" name="cantidad"
                                                        value="{{ $linea['cantidad'] }}"
                                                        min="1" max="{{ config('tienda.maximo_por_producto') }}" required>
                                                    <button type="submit" class="btn btn-sm btn-outline-tienda" title="Actualizar cantidad">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                        <span class="visually-hidden">Actualizar cantidad</span>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-end">${{ number_format($linea['subtotal'], 2) }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('carrito.quitar', $linea['producto']) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Quitar del carrito">
                                                        <i class="bi bi-trash"></i>
                                                        <span class="visually-hidden">Quitar {{ $linea['producto']->nombre }}</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Resumen del pedido --}}
                    <div class="col-lg-4">
                        <div class="bg-white rounded-3 p-4">
                            <h5 class="fw-bolder">Resumen</h5>
                            <div class="d-flex justify-content-between fs-5 my-3">
                                <span>Total</span>
                                <span class="precio">${{ number_format($total, 2) }}</span>
                            </div>
                            {{-- En la Etapa 5 este botón llevará al checkout --}}
                            <button type="button" class="btn btn-tienda w-100 mb-2" disabled>Continuar con el pedido</button>
                            <a class="btn btn-outline-tienda w-100" href="{{ route('menu') }}">Seguir comprando</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layout-tienda>
