<?php

use App\Models\Producto;

/*
| Pruebas del carrito de invitado (se guarda en la sesión como [id del producto => cantidad]).
| RefreshDatabase ya viene de tests/Pest.php.
*/

beforeEach(function () {
    $this->taco = Producto::factory()->create(['nombre' => 'Taco al pastor', 'precio' => 20, 'disponible' => true]);
});

/* ---------- Ver el carrito ---------- */

test('un invitado ve su carrito vacío', function () {
    // Cuando
    $respuesta = $this->get(route('carrito.index'));

    // Entonces
    $respuesta->assertOk()->assertSee('Tu carrito está vacío');
});

test('el carrito muestra cada producto con su subtotal y el total', function () {
    // Dado: el precio se lee de la base de datos, no de la sesión
    $agua = Producto::factory()->create(['nombre' => 'Agua de jamaica', 'precio' => 25.50, 'disponible' => true]);

    // Cuando
    $respuesta = $this->withSession(['carrito' => [$this->taco->id => 3, $agua->id => 2]])
        ->get(route('carrito.index'));

    // Entonces: 3 x $20 = $60, 2 x $25.50 = $51, total $111
    $respuesta->assertOk()
        ->assertSeeInOrder(['Agua de jamaica', '$25.50', '$51.00', 'Taco al pastor', '$20.00', '$60.00', '$111.00']);
});

test('el carrito quita los productos que se agotaron o se borraron', function () {
    // Dado
    $agotado = Producto::factory()->create(['nombre' => 'Pozole', 'disponible' => false]);
    $borrado = Producto::factory()->create(['nombre' => 'Enchiladas', 'disponible' => true]);
    $borrado->delete();

    // Cuando
    $respuesta = $this->withSession(['carrito' => [$this->taco->id => 1, $agotado->id => 2, $borrado->id => 1]])
        ->get(route('carrito.index'));

    // Entonces: solo queda el taco y se avisa al cliente
    $respuesta->assertOk()
        ->assertSee('Taco al pastor')
        ->assertDontSee('Pozole')
        ->assertDontSee('Enchiladas')
        ->assertSee('ya no están disponibles')
        ->assertSessionHas('carrito', [$this->taco->id => 1]);
});

/* ---------- Agregar ---------- */

test('un invitado agrega un producto y regresa a la página donde estaba', function () {
    // Cuando
    $respuesta = $this->from(route('menu'))
        ->post(route('carrito.agregar', $this->taco), ['cantidad' => 2]);

    // Entonces
    $respuesta->assertRedirect(route('menu'))
        ->assertSessionHas('exito')
        ->assertSessionHas('carrito', [$this->taco->id => 2]);
});

test('al agregar un producto que ya estaba se suman las cantidades', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 2]]);

    // Cuando
    $respuesta = $this->post(route('carrito.agregar', $this->taco), ['cantidad' => 3]);

    // Entonces
    $respuesta->assertSessionHas('carrito', [$this->taco->id => 5]);
});

test('no se puede agregar un producto agotado', function () {
    // Dado
    $agotado = Producto::factory()->create(['disponible' => false]);

    // Cuando
    $respuesta = $this->post(route('carrito.agregar', $agotado), ['cantidad' => 1]);

    // Entonces
    $respuesta->assertSessionHas('error')->assertSessionMissing('carrito');
});

test('no se puede pasar del máximo de piezas por producto', function () {
    // Dado: ya tiene 18 y el máximo es 20
    $this->withSession(['carrito' => [$this->taco->id => 18]]);

    // Cuando
    $respuesta = $this->post(route('carrito.agregar', $this->taco), ['cantidad' => 3]);

    // Entonces: no cambia nada
    $respuesta->assertSessionHas('error')
        ->assertSessionHas('carrito', [$this->taco->id => 18]);
});

test('no se agrega con una cantidad inválida', function ($cantidad) {
    // Cuando
    $respuesta = $this->post(route('carrito.agregar', $this->taco), ['cantidad' => $cantidad]);

    // Entonces
    $respuesta->assertSessionHasErrors('cantidad')->assertSessionMissing('carrito');
})->with([
    'vacía' => [null],
    'cero' => [0],
    'negativa' => [-1],
    'texto' => ['abc'],
    'decimal' => [1.5],
    'mayor al máximo' => [21],
]);

test('el error de validación se muestra en la página', function () {
    // Cuando
    $respuesta = $this->from(route('menu'))->followingRedirects()
        ->post(route('carrito.agregar', $this->taco), ['cantidad' => 0]);

    // Entonces
    $respuesta->assertOk()->assertSee('La cantidad mínima es 1.');
});

test('un producto borrado no se puede agregar', function () {
    // Dado
    $this->taco->delete();

    // Cuando / Entonces: la ruta no encuentra productos con soft delete
    $this->post(route('carrito.agregar', $this->taco), ['cantidad' => 1])->assertNotFound();
});

test('el botón de agregar solo aparece en productos disponibles', function () {
    // Dado
    $agotado = Producto::factory()->create(['disponible' => false]);

    // Cuando
    $respuesta = $this->get(route('menu'));

    // Entonces
    $respuesta->assertOk()
        ->assertSee(route('carrito.agregar', $this->taco), false)
        ->assertDontSee(route('carrito.agregar', $agotado), false);
});

/* ---------- Cambiar cantidad ---------- */

test('al actualizar se reemplaza la cantidad, no se suma', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 2]]);

    // Cuando
    $respuesta = $this->patch(route('carrito.actualizar', $this->taco), ['cantidad' => 7]);

    // Entonces
    $respuesta->assertRedirect(route('carrito.index'))
        ->assertSessionHas('exito')
        ->assertSessionHas('carrito', [$this->taco->id => 7]);
});

test('no se actualiza con una cantidad inválida', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 2]]);

    // Cuando
    $respuesta = $this->patch(route('carrito.actualizar', $this->taco), ['cantidad' => 99]);

    // Entonces
    $respuesta->assertSessionHasErrors('cantidad')
        ->assertSessionHas('carrito', [$this->taco->id => 2]);
});

test('no se actualiza un producto que no está en el carrito', function () {
    // Cuando
    $respuesta = $this->patch(route('carrito.actualizar', $this->taco), ['cantidad' => 2]);

    // Entonces
    $respuesta->assertRedirect(route('carrito.index'))
        ->assertSessionHas('error')
        ->assertSessionMissing('carrito');
});

/* ---------- Quitar ---------- */

test('un invitado quita un producto y los demás se quedan', function () {
    // Dado
    $agua = Producto::factory()->create(['disponible' => true]);
    $this->withSession(['carrito' => [$this->taco->id => 2, $agua->id => 1]]);

    // Cuando
    $respuesta = $this->delete(route('carrito.quitar', $this->taco));

    // Entonces
    $respuesta->assertRedirect(route('carrito.index'))
        ->assertSessionHas('exito')
        ->assertSessionHas('carrito', [$agua->id => 1]);
});

/* ---------- Contador de la navbar ---------- */

test('el contador no aparece con el carrito vacío', function () {
    $this->get(route('menu'))->assertOk()->assertDontSee('productos en el carrito');
});

test('el contador suma las piezas de todos los productos', function () {
    // Dado: 3 tacos y 2 aguas son 5 piezas (no 2 productos)
    $agua = Producto::factory()->create(['disponible' => true]);

    // Cuando
    $respuesta = $this->withSession(['carrito' => [$this->taco->id => 3, $agua->id => 2]])
        ->get(route('home'));

    // Entonces
    $respuesta->assertSeeInOrder(['Carrito', '5', 'productos en el carrito']);
});

test('el contador se actualiza al agregar y al quitar', function () {
    // Cuando agrega
    $this->post(route('carrito.agregar', $this->taco), ['cantidad' => 4]);

    // Entonces
    $this->get(route('menu'))->assertSeeInOrder(['Carrito', '4', 'productos en el carrito']);

    // Cuando quita
    $this->delete(route('carrito.quitar', $this->taco));

    // Entonces
    $this->get(route('menu'))->assertDontSee('productos en el carrito');
});
