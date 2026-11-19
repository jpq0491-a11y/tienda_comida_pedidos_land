@props(['titulo'])

{{-- Encabezado de cada página; en el slot van botones opcionales (por ejemplo "Nueva categoría") --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ $titulo }}</h1>
    <div>{{ $slot }}</div>
</div>
