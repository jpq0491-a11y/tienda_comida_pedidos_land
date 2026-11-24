<?php

use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/*
| Pruebas del CRUD de productos del panel admin.
| RefreshDatabase ya viene de tests/Pest.php, así que cada test empieza con la BD vacía.
*/

beforeEach(function () {
    // Disco falso: las imágenes no se guardan de verdad en storage/app/public
    Storage::fake('public');

    $this->admin = User::factory()->admin()->create();
    $this->categoria = Categoria::factory()->create();
});

afterEach(function () {
    // Borra los archivos reales que crea archivoReal()
    File::deleteDirectory(storage_path('framework/testing/subidas'));
});

// Datos válidos para el formulario; cada test cambia solo lo que necesita
function datosProducto(array $cambios = []): array
{
    return array_merge([
        'categoria_id' => test()->categoria->id,
        'nombre' => 'Taco al pastor',
        'descripcion' => 'Con piña y cebolla',
        'precio' => '25.50',
        'disponible' => '1',
    ], $cambios);
}

// UploadedFile::fake() decide el tipo por el NOMBRE del archivo. Para probar la seguridad
// se necesita un archivo real, porque Laravel revisa el CONTENIDO.
function archivoReal(string $nombre, string $contenido): UploadedFile
{
    $carpeta = storage_path('framework/testing/subidas');
    File::ensureDirectoryExists($carpeta);
    $ruta = $carpeta.'/'.uniqid();
    file_put_contents($ruta, $contenido);

    return new UploadedFile($ruta, $nombre, null, null, true);
}

// PNG real de 1x1 pixel y un PHP inofensivo
function contenidoPng(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
}

function contenidoPhp(): string
{
    return '<?php echo "hola"; ?>';
}

/* ---------- Listado y formularios ---------- */

test('el admin ve el listado con categoría y etiquetas', function () {
    // Dado
    $producto = Producto::factory()->for($this->categoria)->create(['nombre' => 'Torta de milanesa']);
    $producto->etiquetas()->attach(Etiqueta::create(['descripcion' => 'Picante']));

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.productos.index'));

    // Entonces
    $respuesta->assertOk()
        ->assertSee('Torta de milanesa')
        ->assertSee($this->categoria->nombre)
        ->assertSee('Picante');
});

test('el admin ve el detalle de un producto con su categoría, etiquetas e imagen', function () {
    // Dado
    $imagen = UploadedFile::fake()->image('torta.jpg')->store('productos', 'public');
    $producto = Producto::factory()->for($this->categoria)->create([
        'nombre' => 'Torta de milanesa',
        'descripcion' => 'Con aguacate y frijoles',
        'precio' => '65.00',
        'imagen' => $imagen,
    ]);
    $producto->etiquetas()->attach(Etiqueta::create(['descripcion' => 'Picante']));

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.productos.show', $producto));

    // Entonces
    $respuesta->assertOk()
        ->assertSee('Torta de milanesa')
        ->assertSee('Con aguacate y frijoles')
        ->assertSee('$65.00')
        ->assertSee($this->categoria->nombre)
        ->assertSee('Picante')
        ->assertSee(Storage::url($imagen));
});

test('el detalle de un producto borrado da 404', function () {
    // Dado: un producto con soft delete
    $producto = Producto::factory()->create();
    $producto->delete();

    // Cuando / Entonces: la ruta no encuentra productos borrados
    $this->actingAs($this->admin)->get(route('admin.productos.show', $producto))->assertNotFound();
});

test('el admin ve los formularios de crear y editar', function () {
    $producto = Producto::factory()->create();

    $this->actingAs($this->admin)->get(route('admin.productos.create'))
        ->assertOk()
        ->assertSee('multipart/form-data', false)
        ->assertSee('name="etiquetas[]"', false);

    $this->actingAs($this->admin)->get(route('admin.productos.edit', $producto))
        ->assertOk()
        ->assertSee($producto->nombre);
});

/* ---------- Crear ---------- */

test('el admin crea un producto con etiquetas e imagen', function () {
    // Dado
    $picante = Etiqueta::create(['descripcion' => 'Picante']);
    $vegetariano = Etiqueta::create(['descripcion' => 'Vegetariano']);

    // Cuando
    $respuesta = $this->actingAs($this->admin)->post(route('admin.productos.store'), datosProducto([
        'etiquetas' => [$picante->id, $vegetariano->id],
        'imagen' => UploadedFile::fake()->image('taco.jpg'),
    ]));

    // Entonces
    $respuesta->assertRedirect(route('admin.productos.index'))
        ->assertSessionHas('exito');

    $producto = Producto::first();
    expect($producto->nombre)->toBe('Taco al pastor')
        ->and($producto->etiquetas->pluck('id')->sort()->values()->all())->toBe([$picante->id, $vegetariano->id]);

    // La BD guarda la ruta relativa con nombre aleatorio, no el nombre original
    expect($producto->imagen)->toStartWith('productos/')->not->toContain('taco');
    Storage::disk('public')->assertExists($producto->imagen);
});

test('el admin crea un producto sin etiquetas ni imagen', function () {
    $this->actingAs($this->admin)->post(route('admin.productos.store'), datosProducto())
        ->assertSessionHasNoErrors();

    $producto = Producto::first();
    expect($producto->imagen)->toBeNull()
        ->and($producto->etiquetas)->toHaveCount(0);
});

test('no se crea un producto con datos inválidos', function () {
    $this->actingAs($this->admin)->post(route('admin.productos.store'), [
        'categoria_id' => 999,
        'nombre' => '',
        'precio' => '25.999',
        'etiquetas' => [999],
    ])->assertSessionHasErrors(['categoria_id', 'nombre', 'precio', 'etiquetas.0']);

    $this->assertDatabaseCount('productos', 0);
});

test('no se repite el nombre de un producto activo, pero sí el de uno borrado', function () {
    // Dado
    $producto = Producto::factory()->create(['nombre' => 'Taco al pastor']);

    // Cuando / Entonces: con el producto activo, el nombre está ocupado
    $this->actingAs($this->admin)->post(route('admin.productos.store'), datosProducto())
        ->assertSessionHasErrors(['nombre' => 'Ya existe un producto con ese nombre.']);

    // Cuando / Entonces: después del soft delete, el nombre se puede volver a usar
    $producto->delete();
    $this->actingAs($this->admin)->post(route('admin.productos.store'), datosProducto())
        ->assertSessionHasNoErrors();

    expect(Producto::withTrashed()->where('nombre', 'Taco al pastor')->count())->toBe(2);
});

/* ---------- Seguridad de la imagen ---------- */

test('rechaza archivos que no son imágenes válidas', function (UploadedFile $archivo) {
    // Cuando
    $respuesta = $this->actingAs($this->admin)->post(route('admin.productos.store'), datosProducto([
        'imagen' => $archivo,
    ]));

    // Entonces: error de validación, no se crea el producto y no se guarda ningún archivo
    $respuesta->assertSessionHasErrors('imagen');
    $this->assertDatabaseCount('productos', 0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'archivo .php' => fn () => archivoReal('malo.php', contenidoPhp()),
    'PHP renombrado a .jpg' => fn () => archivoReal('virus.jpg', contenidoPhp()),
    'imagen real llamada .php' => fn () => archivoReal('foto.php', contenidoPng()),
    'imagen con PHP pegado llamada .php' => fn () => archivoReal('foto.php', contenidoPng().contenidoPhp()),
    'archivo .phtml' => fn () => archivoReal('x.phtml', contenidoPhp()),
    'SVG con JavaScript' => fn () => archivoReal('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
    'PDF' => fn () => UploadedFile::fake()->create('menu.pdf', 10, 'application/pdf'),
    'imagen de más de 2 MB' => fn () => UploadedFile::fake()->image('grande.jpg')->size(3000),
]);

test('una imagen con PHP escondido se guarda como .png, nunca como .php', function () {
    $this->actingAs($this->admin)->post(route('admin.productos.store'), datosProducto([
        'imagen' => archivoReal('foto.jpg', contenidoPng().contenidoPhp()),
    ]))->assertSessionHasNoErrors();

    // La extensión la decide Laravel por el contenido, así que el servidor nunca la ejecuta como PHP
    expect(Producto::first()->imagen)->toEndWith('.png');
});

/* ---------- Editar ---------- */

test('el admin edita un producto y cambia sus etiquetas', function () {
    // Dado
    $picante = Etiqueta::create(['descripcion' => 'Picante']);
    $vegetariano = Etiqueta::create(['descripcion' => 'Vegetariano']);
    $producto = Producto::factory()->create();
    $producto->etiquetas()->attach($picante);

    // Cuando
    $respuesta = $this->actingAs($this->admin)->put(route('admin.productos.update', $producto), datosProducto([
        'nombre' => 'Taco de suadero',
        'etiquetas' => [$vegetariano->id],
    ]));

    // Entonces: sync deja solo la etiqueta elegida
    $respuesta->assertRedirect(route('admin.productos.index'))->assertSessionHas('exito');
    $producto->refresh();
    expect($producto->nombre)->toBe('Taco de suadero')
        ->and($producto->etiquetas->pluck('id')->all())->toBe([$vegetariano->id]);
});

test('al editar sin elegir etiquetas se quitan todas', function () {
    $producto = Producto::factory()->create();
    $producto->etiquetas()->attach(Etiqueta::create(['descripcion' => 'Picante']));

    $this->actingAs($this->admin)->put(route('admin.productos.update', $producto), datosProducto())
        ->assertSessionHasNoErrors();

    expect($producto->fresh()->etiquetas)->toHaveCount(0);
});

test('al editar se puede dejar el mismo nombre, pero no el de otro producto', function () {
    $producto = Producto::factory()->create(['nombre' => 'Taco al pastor']);
    $otro = Producto::factory()->create(['nombre' => 'Torta de milanesa']);

    $this->actingAs($this->admin)->put(route('admin.productos.update', $producto), datosProducto())
        ->assertSessionHasNoErrors();

    $this->actingAs($this->admin)->put(route('admin.productos.update', $otro), datosProducto())
        ->assertSessionHasErrors('nombre');
});

test('al subir una imagen nueva se borra la anterior', function () {
    // Dado: un producto que ya tiene imagen
    $anterior = UploadedFile::fake()->image('vieja.jpg')->store('productos', 'public');
    $producto = Producto::factory()->create(['imagen' => $anterior]);

    // Cuando
    $this->actingAs($this->admin)->put(route('admin.productos.update', $producto), datosProducto([
        'imagen' => UploadedFile::fake()->image('nueva.png'),
    ]))->assertSessionHasNoErrors();

    // Entonces: no queda el archivo huérfano y la nueva sí existe
    $producto->refresh();
    Storage::disk('public')->assertMissing($anterior);
    Storage::disk('public')->assertExists($producto->imagen);
    expect($producto->imagen)->not->toBe($anterior);
});

test('al editar sin imagen nueva se conserva la que tenía', function () {
    $anterior = UploadedFile::fake()->image('vieja.jpg')->store('productos', 'public');
    $producto = Producto::factory()->create(['imagen' => $anterior]);

    $this->actingAs($this->admin)->put(route('admin.productos.update', $producto), datosProducto())
        ->assertSessionHasNoErrors();

    expect($producto->fresh()->imagen)->toBe($anterior);
    Storage::disk('public')->assertExists($anterior);
});

/* ---------- Eliminar ---------- */

test('el admin elimina un producto con soft delete y se conserva su imagen', function () {
    // Dado
    $imagen = UploadedFile::fake()->image('taco.jpg')->store('productos', 'public');
    $producto = Producto::factory()->create(['imagen' => $imagen]);

    // Cuando
    $respuesta = $this->actingAs($this->admin)->delete(route('admin.productos.destroy', $producto));

    // Entonces: sigue en la tabla con deleted_at y la imagen sigue en el disco por si se restaura
    $respuesta->assertRedirect(route('admin.productos.index'))->assertSessionHas('exito');
    $this->assertSoftDeleted($producto);
    Storage::disk('public')->assertExists($imagen);
});
