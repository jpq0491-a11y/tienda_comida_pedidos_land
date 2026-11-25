{{-- Correo en Markdown: Laravel lo convierte en HTML con su plantilla (botones, tablas, pie) --}}
<x-mail::message>
# Llegó un pedido nuevo

**Folio:** {{ $pedido->folio }}<br>
**Fecha:** {{ $pedido->created_at->format('d/m/Y H:i') }}<br>
**Cliente:** {{ $pedido->nombre_cliente }}<br>
**Teléfono:** {{ $pedido->telefono }}<br>
@if ($pedido->tipo_entrega === 'domicilio')
**Entrega:** A domicilio<br>
**Dirección:** {{ $pedido->direccion_completa }}<br>
@if ($pedido->referencia)
**Referencia:** {{ $pedido->referencia }}<br>
@endif
**Ubicación:** [Ver en Google Maps](https://www.google.com/maps/search/?api=1&query={{ $pedido->latitud }},{{ $pedido->longitud }})
({{ number_format($pedido->distancia_km, 1) }} km en línea recta desde la sucursal)<br>
**Pago:** Efectivo, {{ $pedido->cambio !== null ? 'paga con $'.number_format($pedido->paga_con, 2).' (llevar $'.number_format($pedido->cambio, 2).' de cambio)' : 'con el monto exacto' }}
@else
**Entrega:** Recoger en {{ $pedido->sucursal->nombre }}<br>
**Pago:** Efectivo
@endif

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
