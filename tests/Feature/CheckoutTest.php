<?php

use App\Mail\PedidoNuevo;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/*
| Pruebas del checkout: formulario, creación del pedido, página de confirmación
| y correo al administrador. RefreshDatabase ya viene de tests/Pest.php.
*/

beforeEach(function () {
    // Mail::fake(): ningún correo sale de verdad, solo se registra para revisarlo
    Mail::fake();

    $this->sucursal = Sucursal::factory()->create(['nombre' => 'Sucursal Centro']);
    $this->taco = Producto::factory()->create(['nombre' => 'Taco al pastor', 'precio' => 20, 'disponible' => true]);
    $this->agua = Producto::factory()->create(['nombre' => 'Agua de jamaica', 'precio' => 25.50, 'disponible' => true]);

    $this->datos = [
        'nombre_cliente' => 'Ana López',
        'telefono' => '5512345678',
        'notas' => 'Sin cebolla',
    ];
});

/* ---------- Formulario ---------- */

test('con el carrito vacío no se puede entrar al checkout', function () {
    // Cuando
    $respuesta = $this->get(route('checkout.create'));

    // Entonces
    $respuesta->assertRedirect(route('carrito.index'))->assertSessionHas('error');
});

test('el checkout muestra el resumen del pedido y la sucursal', function () {
    // Cuando: 3 x $20 = $60, 2 x $25.50 = $51
    $respuesta = $this->withSession(['carrito' => [$this->taco->id => 3, $this->agua->id => 2]])
        ->get(route('checkout.create'));

    // Entonces
    $respuesta->assertOk()
        ->assertSeeInOrder(['2 × Agua de jamaica', '$51.00', '3 × Taco al pastor', '$60.00', '$111.00'])
        ->assertSee('Sucursal Centro');
});

/* ---------- Crear el pedido ---------- */

test('un invitado hace un pedido y se guarda con sus productos', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 3, $this->agua->id => 2]]);

    // Cuando
    $respuesta = $this->post(route('checkout.store'), $this->datos);

    // Entonces: el pedido tiene los datos del formulario y los que decide el servidor
    $pedido = Pedido::sole();
    expect($pedido)
        ->folio->toMatch('/^SC-[A-Z0-9]{8}$/')
        ->nombre_cliente->toBe('Ana López')
        ->telefono->toBe('5512345678')
        ->notas->toBe('Sin cebolla')
        ->sucursal_id->toBe($this->sucursal->id)
        ->user_id->toBeNull()
        ->tipo_entrega->toBe('recoger')
        ->metodo_pago->toBe('efectivo')
        ->estado->toBe('pendiente')
        ->total->toBe('111.00');

    // Cada producto queda en la tabla pivote con su cantidad y precio
    $this->assertDatabaseHas('detalle_pedidos', [
        'pedido_id' => $pedido->id, 'producto_id' => $this->taco->id,
        'cantidad' => 3, 'precio_unitario' => 20, 'subtotal' => 60,
    ]);
    $this->assertDatabaseHas('detalle_pedidos', [
        'pedido_id' => $pedido->id, 'producto_id' => $this->agua->id,
        'cantidad' => 2, 'precio_unitario' => 25.50, 'subtotal' => 51,
    ]);

    // Se vacía el carrito y el cliente va a la página de su pedido
    $respuesta->assertRedirect(route('pedido.confirmacion', $pedido))
        ->assertSessionHas('exito')
        ->assertSessionMissing('carrito');
});

test('el total y los precios se calculan en el servidor, no se toman del formulario', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando: alguien manda un total y un estado falsos
    $this->post(route('checkout.store'), $this->datos + ['total' => 1, 'estado' => 'entregado', 'precio' => 0]);

    // Entonces: se ignoran
    expect(Pedido::sole())->total->toBe('20.00')->estado->toBe('pendiente');
});

test('cada pedido recibe un folio distinto', function () {
    // Cuando: dos pedidos seguidos
    $this->withSession(['carrito' => [$this->taco->id => 1]])->post(route('checkout.store'), $this->datos);
    $this->withSession(['carrito' => [$this->taco->id => 1]])->post(route('checkout.store'), $this->datos);

    // Entonces
    expect(Pedido::pluck('folio')->unique())->toHaveCount(2);
});

test('con el carrito vacío no se crea ningún pedido', function () {
    // Cuando
    $respuesta = $this->post(route('checkout.store'), $this->datos);

    // Entonces
    $respuesta->assertRedirect(route('carrito.index'))->assertSessionHas('error');
    expect(Pedido::count())->toBe(0);
});

test('si un producto se agotó mientras llenaba el formulario, vuelve al checkout sin crear el pedido', function () {
    // Dado: el agua se agotó después de agregarla al carrito
    $this->withSession(['carrito' => [$this->taco->id => 1, $this->agua->id => 1]]);
    $this->agua->update(['disponible' => false]);

    // Cuando
    $respuesta = $this->post(route('checkout.store'), $this->datos);

    // Entonces: conserva lo que escribió y el carrito queda sin el producto agotado
    $respuesta->assertRedirect(route('checkout.create'))
        ->assertSessionHas('error')
        ->assertSessionHasInput('nombre_cliente', 'Ana López')
        ->assertSessionHas('carrito', [$this->taco->id => 1]);
    expect(Pedido::count())->toBe(0);
    Mail::assertNothingSent();
});

/* ---------- Validación ---------- */

test('no se crea el pedido con datos inválidos', function (string $campo, mixed $valor) {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $respuesta = $this->post(route('checkout.store'), [$campo => $valor] + $this->datos);

    // Entonces: el carrito sigue igual para que lo intente de nuevo
    $respuesta->assertSessionHasErrors($campo)->assertSessionHas('carrito', [$this->taco->id => 1]);
    expect(Pedido::count())->toBe(0);
    Mail::assertNothingSent();
})->with([
    'sin nombre' => ['nombre_cliente', ''],
    'nombre muy largo' => ['nombre_cliente', str_repeat('a', 101)],
    'sin teléfono' => ['telefono', ''],
    'teléfono corto' => ['telefono', '55123'],
    'teléfono con letras' => ['telefono', '55123abcde'],
    'teléfono con guiones' => ['telefono', '55-1234-56'],
    'notas muy largas' => ['notas', str_repeat('a', 501)],
]);

test('las notas son opcionales', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $this->post(route('checkout.store'), ['notas' => ''] + $this->datos);

    // Entonces
    expect(Pedido::sole()->notas)->toBeNull();
});

test('el error de validación se muestra en el formulario', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $respuesta = $this->from(route('checkout.create'))->followingRedirects()
        ->post(route('checkout.store'), ['telefono' => '123'] + $this->datos);

    // Entonces
    $respuesta->assertOk()->assertSee('El teléfono debe tener exactamente 10 dígitos');
});

/* ---------- Página de confirmación ---------- */

test('la confirmación muestra el pedido sin el teléfono del cliente', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 3, $this->agua->id => 2]])
        ->post(route('checkout.store'), $this->datos);
    $pedido = Pedido::sole();

    // Cuando
    $respuesta = $this->get(route('pedido.confirmacion', $pedido));

    // Entonces: cualquiera con el enlace puede verla, por eso no lleva el teléfono
    $respuesta->assertOk()
        ->assertSee($pedido->folio)
        ->assertSee('Ana López')
        ->assertSee('Sin cebolla')
        ->assertSee('Sucursal Centro')
        ->assertSee('Pendiente')
        ->assertSeeInOrder(['2 × Agua de jamaica', '$25.50 c/u', '$51.00', '3 × Taco al pastor', '$60.00', '$111.00'])
        ->assertDontSee('5512345678');
});

test('la confirmación muestra el precio cobrado aunque el producto cambie o se borre', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]])->post(route('checkout.store'), $this->datos);
    $this->taco->update(['precio' => 99]);
    $this->taco->delete();

    // Cuando
    $respuesta = $this->get(route('pedido.confirmacion', Pedido::sole()));

    // Entonces: el precio sale de detalle_pedidos, no del producto
    $respuesta->assertOk()
        ->assertSee('Taco al pastor')
        ->assertSee('$20.00')
        ->assertDontSee('$99.00');
});

test('la confirmación solo se abre con el folio, no con el id', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]])->post(route('checkout.store'), $this->datos);

    // Cuando / Entonces
    $this->get('/pedido/SC-NOEXISTE')->assertNotFound();
    $this->get('/pedido/'.Pedido::sole()->id)->assertNotFound();
});

/* ---------- Correo al administrador ---------- */

test('al crear un pedido se manda un correo a cada administrador', function () {
    // Dado
    $admin = User::factory()->admin()->create();
    $otroAdmin = User::factory()->admin()->create();
    $usuarioNormal = User::factory()->create();

    // Cuando
    $this->withSession(['carrito' => [$this->taco->id => 1]])->post(route('checkout.store'), $this->datos);

    // Entonces: un solo correo para los admins, con el pedido que se acaba de crear
    Mail::assertSent(PedidoNuevo::class, 1);
    Mail::assertSent(PedidoNuevo::class, fn (PedidoNuevo $correo) => $correo->hasTo($admin->email)
        && $correo->hasTo($otroAdmin->email)
        && ! $correo->hasTo($usuarioNormal->email)
        && $correo->pedido->is(Pedido::sole()));
});

test('el correo trae los datos del pedido', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 3]])->post(route('checkout.store'), $this->datos);
    $pedido = Pedido::sole();

    // Cuando
    $correo = new PedidoNuevo($pedido);

    // Entonces: el admin sí ve el teléfono para poder llamar al cliente
    $correo->assertHasSubject("Pedido nuevo {$pedido->folio} por \$60.00");
    $correo->assertSeeInHtml($pedido->folio)
        ->assertSeeInHtml('Ana López')
        ->assertSeeInHtml('5512345678')
        ->assertSeeInHtml('Sucursal Centro')
        ->assertSeeInHtml('Taco al pastor')
        ->assertSeeInHtml('$60.00')
        ->assertSeeInHtml('Sin cebolla')
        ->assertSeeInHtml(route('pedido.confirmacion', $pedido));
});

test('si no hay administradores el pedido se guarda sin mandar correo', function () {
    // Cuando
    $respuesta = $this->withSession(['carrito' => [$this->taco->id => 1]])
        ->post(route('checkout.store'), $this->datos);

    // Entonces
    $respuesta->assertRedirect(route('pedido.confirmacion', Pedido::sole()));
    Mail::assertNothingSent();
});

test('si el correo falla el pedido se guarda y el cliente llega a su confirmación', function () {
    // Dado: el servidor de correo no responde
    User::factory()->admin()->create();
    Mail::shouldReceive('to')->andThrow(new RuntimeException('No se pudo conectar al servidor de correo'));

    // Cuando
    $respuesta = $this->withSession(['carrito' => [$this->taco->id => 1]])
        ->post(route('checkout.store'), $this->datos);

    // Entonces: el cliente no ve un error y no vuelve a pedir lo mismo
    $respuesta->assertRedirect(route('pedido.confirmacion', Pedido::sole()))
        ->assertSessionMissing('carrito');
});
