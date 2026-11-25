{{-- Correo en Markdown: Laravel lo convierte en HTML con su plantilla (botones, tablas, pie) --}}
<x-mail::message>
# Llegó un pedido nuevo

**Folio:** {{ $pedido->folio }}<br>
**Fecha:** {{ $pedido->created_at->format('d/m/Y H:i') }}<br>
**Cliente:** {{ $pedido->nombre_cliente }}<br>
**Teléfono:** {{ $pedido->telefono }}<br>
**Entrega:** Recoger en {{ $pedido->sucursal->nombre }}<br>
**Pago:** Efectivo

{{-- Las tablas Markdown no deben llevar sangría o se muestran como texto --}}
<x-mail::table>
| Producto | Cantidad | Subtotal |
|:---------|:--------:|---------:|
@foreach ($pedido->productos as $producto)
| {{ $producto->nombre }} | {{ $producto->pivot->cantidad }} | ${{ number_format($producto->pivot->subtotal, 2) }} |
@endforeach
| **Total** | | **${{ number_format($pedido->total, 2) }}** |
</x-mail::table>

@if ($pedido->notas)
<x-mail::panel>
**Notas del cliente:** {{ $pedido->notas }}
</x-mail::panel>
@endif

{{-- En la Etapa 7 este botón llevará al detalle del pedido en el panel de administración --}}
<x-mail::button :url="route('pedido.confirmacion', $pedido)" color="success">
Ver pedido
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
