<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;

/*
| Acceso al panel admin: las rutas están protegidas con los middleware 'auth'
| (hay que iniciar sesión) y 'can:administrar' (el Gate que solo deja pasar al admin).
*/

beforeEach(function () {
    $this->categoria = Categoria::factory()->create();
    $this->producto = Producto::factory()->for($this->categoria)->create();
});

// Todas las rutas del panel: [método, nombre de la ruta, modelo que recibe]
dataset('rutas del panel', [
    'inicio' => ['get', 'admin.inicio', null],
    'categorías: listado' => ['get', 'admin.categorias.index', null],
    'categorías: crear' => ['get', 'admin.categorias.create', null],
    'categorías: guardar' => ['post', 'admin.categorias.store', null],
    'categorías: editar' => ['get', 'admin.categorias.edit', 'categoria'],
    'categorías: actualizar' => ['put', 'admin.categorias.update', 'categoria'],
    'categorías: borrar' => ['delete', 'admin.categorias.destroy', 'categoria'],
    'productos: listado' => ['get', 'admin.productos.index', null],
    'productos: crear' => ['get', 'admin.productos.create', null],
    'productos: guardar' => ['post', 'admin.productos.store', null],
    'productos: detalle' => ['get', 'admin.productos.show', 'producto'],
    'productos: editar' => ['get', 'admin.productos.edit', 'producto'],
    'productos: actualizar' => ['put', 'admin.productos.update', 'producto'],
    'productos: borrar' => ['delete', 'admin.productos.destroy', 'producto'],
]);

// Arma la URL; los modelos se crean en beforeEach, por eso el dataset solo trae su nombre
function urlPanel(string $ruta, ?string $modelo): string
{
    return $modelo ? route($ruta, test()->{$modelo}) : route($ruta);
}

test('un invitado es mandado al login', function (string $metodo, string $ruta, ?string $modelo) {
    // Cuando: entra sin iniciar sesión
    $respuesta = $this->{$metodo}(urlPanel($ruta, $modelo));

    // Entonces
    $respuesta->assertRedirect(route('login'));
})->with('rutas del panel');

test('un usuario que no es admin recibe 403', function (string $metodo, string $ruta, ?string $modelo) {
    // Dado: un usuario normal (sin rol admin)
    $usuario = User::factory()->create();

    // Cuando
    $respuesta = $this->actingAs($usuario)->{$metodo}(urlPanel($ruta, $modelo));

    // Entonces: el Gate 'administrar' lo bloquea
    $respuesta->assertForbidden();
})->with('rutas del panel');

test('el admin sí entra a las páginas del panel', function (string $ruta, ?string $modelo) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(urlPanel($ruta, $modelo))->assertOk();
})->with([
    'inicio' => ['admin.inicio', null],
    'categorías: listado' => ['admin.categorias.index', null],
    'categorías: crear' => ['admin.categorias.create', null],
    'categorías: editar' => ['admin.categorias.edit', 'categoria'],
    'productos: listado' => ['admin.productos.index', null],
    'productos: crear' => ['admin.productos.create', null],
    'productos: detalle' => ['admin.productos.show', 'producto'],
    'productos: editar' => ['admin.productos.edit', 'producto'],
]);

test('un invitado o un usuario normal no pueden cambiar datos del menú', function () {
    // Dado
    $usuario = User::factory()->create();

    // Cuando: intentan crear, editar y borrar
    $this->post(route('admin.productos.store'), ['nombre' => 'Colado']);
    $this->actingAs($usuario)->put(route('admin.categorias.update', $this->categoria), ['nombre' => 'Hackeada', 'orden' => 1]);
    $this->actingAs($usuario)->delete(route('admin.productos.destroy', $this->producto));

    // Entonces: nada cambió
    $this->assertDatabaseMissing('productos', ['nombre' => 'Colado']);
    expect($this->categoria->fresh()->nombre)->not->toBe('Hackeada');
    $this->assertNotSoftDeleted($this->producto);
});

test('al iniciar sesión el admin va al panel y un cliente a la tienda', function () {
    $admin = User::factory()->admin()->create();
    $usuario = User::factory()->create();

    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('admin.inicio'));

    auth()->logout();

    $this->post(route('login.store'), ['email' => $usuario->email, 'password' => 'password'])
        ->assertRedirect(route('home'));
});
