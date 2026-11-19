{{-- Campos compartidos por crear y editar; $producto es nuevo (crear) o el existente (editar) --}}
<div class="mb-3">
    <label for="categoria_id" class="form-label">Categoría</label>
    <select id="categoria_id" name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
        <option value="">Elige una categoría</option>
        @foreach ($categorias as $categoria)
            <option value="{{ $categoria->id }}" @selected(old('categoria_id', $producto->categoria_id) == $categoria->id)>
                {{ $categoria->nombre }}
            </option>
        @endforeach
    </select>
    @error('categoria_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" id="nombre" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
        value="{{ old('nombre', $producto->nombre) }}" maxlength="100" required>
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="descripcion" class="form-label">Descripción <span class="text-muted">(opcional)</span></label>
    <textarea id="descripcion" name="descripcion" rows="3" maxlength="500"
        class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $producto->descripcion) }}</textarea>
    @error('descripcion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="precio" class="form-label">Precio</label>
    <div class="input-group has-validation">
        <span class="input-group-text">$</span>
        <input type="number" id="precio" name="precio" class="form-control @error('precio') is-invalid @enderror"
            value="{{ old('precio', $producto->precio) }}" min="0.01" max="99999.99" step="0.01" required>
        @error('precio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-check mb-3">
    {{-- Si la casilla no se marca, el navegador no la envía; el campo oculto manda 0 en ese caso --}}
    <input type="hidden" name="disponible" value="0">
    <input type="checkbox" id="disponible" name="disponible" value="1" class="form-check-input"
        @checked(old('disponible', $producto->disponible))>
    <label for="disponible" class="form-check-label">Disponible para venta</label>
</div>
