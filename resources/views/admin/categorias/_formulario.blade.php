{{-- Campos compartidos por crear y editar; $categoria es nueva (crear) o la existente (editar) --}}
<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" id="nombre" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
        value="{{ old('nombre', $categoria->nombre) }}" maxlength="100" required autofocus>
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="orden" class="form-label">Orden en el menú</label>
    <input type="number" id="orden" name="orden" class="form-control @error('orden') is-invalid @enderror"
        value="{{ old('orden', $categoria->orden) }}" min="0" required>
    <div class="form-text">Las categorías se muestran de menor a mayor.</div>
    @error('orden')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
