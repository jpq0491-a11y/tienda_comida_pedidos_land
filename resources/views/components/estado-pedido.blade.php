@props(['estado'])

{{-- Etiqueta de color según el estado del pedido, por ejemplo <x-estado-pedido :estado="$pedido->estado" /> --}}
@php
    $color = match ($estado) {
        'pendiente' => 'warning',
        'preparando' => 'info',
        'en_camino' => 'primary',
        'entregado' => 'success',
        'cancelado' => 'secondary',
        default => 'light',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge text-bg-{$color}"]) }}>{{ ucfirst(str_replace('_', ' ', $estado)) }}</span>
