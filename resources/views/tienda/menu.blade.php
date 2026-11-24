<x-layout-tienda titulo="Menú" descripcion="Conoce nuestro menú de comida casera y haz tu pedido en línea.">
    <header class="hero-tienda py-5">
        <div class="container px-4 px-lg-5 text-center text-white">
            <h1 class="fw-bolder">Nuestro <span>menú</span></h1>
            <p class="lead text-white-50 mb-0">Elige tus platillos favoritos, todo hecho al momento.</p>
        </div>
    </header>

    @if ($categorias->isEmpty())
        <section class="py-5">
            <div class="container text-center">
                <i class="bi bi-journal-x display-4 text-tienda"></i>
                <p class="lead mt-3 mb-0">Por ahora no hay platillos en el menú. Vuelve pronto.</p>
            </div>
        </section>
    @else
        {{-- Accesos rápidos a cada categoría (enlaces a los id de las secciones) --}}
        <nav class="bg-white border-bottom py-3" aria-label="Categorías del menú">
            <div class="container px-4 px-lg-5 d-flex flex-wrap justify-content-center gap-2">
                @foreach ($categorias as $categoria)
                    <a class="btn btn-outline-tienda btn-sm rounded-pill" href="#categoria-{{ $categoria->id }}">
                        {{ $categoria->nombre }}
                    </a>
                @endforeach
            </div>
        </nav>

        @foreach ($categorias as $categoria)
            <section id="categoria-{{ $categoria->id }}" class="py-5">
                <div class="container px-4 px-lg-5">
                    <h2 class="fw-bolder mb-4">{{ $categoria->nombre }}</h2>
                    <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-sm-2 row-cols-xl-4">
                        @foreach ($categoria->productos as $producto)
                            <div class="col mb-5">
                                <x-tarjeta-producto :producto="$producto" :mostrar-categoria="false" />
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach
    @endif
</x-layout-tienda>
