{{-- Lista de errores de validación; no muestra nada si no hay errores --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <p class="mb-1"><strong>Revisa los siguientes datos:</strong></p>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
