<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración</title>
</head>
<body>
    {{-- Vista temporal: en el siguiente paso se pasa al componente layout del panel admin --}}
    <h1>Panel de administración</h1>
    <p>Bienvenido, {{ auth()->user()->name }}.</p>
</body>
</html>
