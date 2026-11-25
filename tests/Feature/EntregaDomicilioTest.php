<?php

use App\Mail\PedidoNuevo;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/*
| Pruebas de la entrega a domicilio: dirección, mapa (latitud y longitud), "¿con cuánto pagas?",
| distancia calculada en el servidor y radio máximo de entrega.
| La parte de recoger en sucursal está en CheckoutTest. RefreshDatabase ya viene de tests/Pest.php.
*/

beforeEach(function () {
    // Mail::fake(): ningún correo sale de verdad, solo se registra para revisarlo
    Mail::fake();

    // La sucursal en Ayotlán, Jalisco, con entrega hasta 3 km
    $this->sucursal = Sucursal::factory()->create([
        'nombre' => 'Sucursal Centro',
        'latitud' => 20.5299754,
        'longitud' => -102.3306392,
        'radio_entrega_km' => 3,
    ]);
    $this->taco = Producto::factory()->create(['nombre' => 'Taco al pastor', 'precio' => 100, 'disponible' => true]);

    // Un punto a 2.23 km de la sucursal (dentro de la zona)
    $this->datos = [
        'nombre_cliente' => 'Ana López',
        'telefono' => '5512345678',
        'tipo_entrega' => 'domicilio',
        'calle' => 'Av. Juárez',
        'numero_exterior' => '120',
        'numero_interior' => '4',
        'colonia' => 'Centro',
        'referencia' => 'Portón verde',
        'latitud' => '20.5480000',
        'longitud' => '-102.3400000',
        'paga_con' => '150',
    ];
});

/* ---------- Formulario ---------- */

test('el checkout muestra la entrega a domicilio y el mapa con la zona de entrega', function () {
    // Cuando
    $respuesta = $this->withSession(['carrito' => [$this->taco->id => 1]])
        ->get(route('checkout.create'));

    // Entonces: el mapa recibe la ubicación y el radio de la sucursal, y el total para el cambio
    $respuesta->assertOk()
        ->assertSee('Entrega a domicilio')
        ->assertSee('¿Con cuánto pagas?')
        ->assertSee('hasta 3 km de la sucursal')
        ->assertSee('data-latitud="20.5299754"', false)
        ->assertSee('data-radio-km="3"', false)
        ->assertSee('data-total="100"', false)
        ->assertSee(asset('vendor/leaflet/leaflet.js'), false);
});

/* ---------- Crear el pedido ---------- */

test('un pedido a domicilio se guarda con su dirección, ubicación, distancia y cambio', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando: alguien manda además una distancia falsa
    $respuesta = $this->post(route('checkout.store'), $this->datos + ['distancia_km' => 0.1]);

    // Entonces: la distancia la calcula el servidor (2.23 km), no se toma del formulario
    $pedido = Pedido::sole();
    expect($pedido)
        ->tipo_entrega->toBe('domicilio')
        ->calle->toBe('Av. Juárez')
        ->numero_exterior->toBe('120')
        ->numero_interior->toBe('4')
        ->colonia->toBe('Centro')
        ->referencia->toBe('Portón verde')
        ->latitud->toBe(20.548)
        ->longitud->toBe(-102.34)
        ->distancia_km->toBe(2.23)
        ->paga_con->toBe('150.00')
        ->total->toBe('100.00')
        ->cambio->toBe(50.0)
        ->direccion_completa->toBe('Av. Juárez 120 int. 4, col. Centro');

    $respuesta->assertRedirect(route('pedido.confirmacion', $pedido))->assertSessionMissing('carrito');
});

test('si no dice con cuánto paga, paga con el monto exacto', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $this->post(route('checkout.store'), ['paga_con' => ''] + $this->datos);

    // Entonces
    expect(Pedido::sole())->paga_con->toBeNull()->cambio->toBeNull();
});

test('el número interior y la referencia son opcionales', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $this->post(route('checkout.store'), ['numero_interior' => '', 'referencia' => ''] + $this->datos);

    // Entonces
    expect(Pedido::sole())
        ->numero_interior->toBeNull()
        ->referencia->toBeNull()
        ->direccion_completa->toBe('Av. Juárez 120, col. Centro');
});

test('puede pagar con el monto exacto aunque el total tenga decimales', function () {
    // Dado: 3 × $33.10 en float da 99.30000000000001
    $agua = Producto::factory()->create(['precio' => 33.10, 'disponible' => true]);
    $this->withSession(['carrito' => [$agua->id => 3]]);

    // Cuando
    $respuesta = $this->post(route('checkout.store'), ['paga_con' => '99.30'] + $this->datos);

    // Entonces
    $respuesta->assertSessionHasNoErrors();
    expect(Pedido::sole())->total->toBe('99.30')->cambio->toBe(0.0);
});

test('al recoger en sucursal se descartan la dirección, la ubicación y con cuánto paga', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando: el formulario manda los datos de domicilio aunque eligió recoger (y una ubicación lejana)
    $this->post(route('checkout.store'), ['tipo_entrega' => 'recoger', 'latitud' => '0', 'longitud' => '0'] + $this->datos)
        ->assertSessionHasNoErrors();

    // Entonces
    expect(Pedido::sole())
        ->tipo_entrega->toBe('recoger')
        ->calle->toBeNull()
        ->colonia->toBeNull()
        ->referencia->toBeNull()
        ->latitud->toBeNull()
        ->longitud->toBeNull()
        ->distancia_km->toBeNull()
        ->paga_con->toBeNull();
});

/* ---------- Validación ---------- */

test('no se crea el pedido a domicilio con datos inválidos', function (string $campo, mixed $valor) {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $respuesta = $this->post(route('checkout.store'), [$campo => $valor] + $this->datos);

    // Entonces: el carrito sigue igual para que lo intente de nuevo
    $respuesta->assertSessionHasErrors($campo)->assertSessionHas('carrito', [$this->taco->id => 1]);
    expect(Pedido::count())->toBe(0);
    Mail::assertNothingSent();
})->with([
    'sin forma de entrega' => ['tipo_entrega', ''],
    'forma de entrega inventada' => ['tipo_entrega', 'dron'],
    'sin calle' => ['calle', ''],
    'calle muy larga' => ['calle', str_repeat('a', 151)],
    'sin número exterior' => ['numero_exterior', ''],
    'número interior muy largo' => ['numero_interior', str_repeat('1', 21)],
    'sin colonia' => ['colonia', ''],
    'referencia muy larga' => ['referencia', str_repeat('a', 256)],
    'sin latitud' => ['latitud', ''],
    'sin longitud' => ['longitud', ''],
    'latitud con letras' => ['latitud', 'abc'],
    'latitud fuera de rango' => ['latitud', '91'],
    'longitud fuera de rango' => ['longitud', '-181'],
    'paga con letras' => ['paga_con', 'doscientos'],
    'paga con 3 decimales' => ['paga_con', '150.555'],
    'paga con demasiado' => ['paga_con', '100000'],
]);

test('sin ubicación en el mapa se pide marcarla', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $respuesta = $this->post(route('checkout.store'), ['latitud' => '', 'longitud' => ''] + $this->datos);

    // Entonces
    $respuesta->assertSessionHasErrors(['latitud' => 'Marca en el mapa dónde te entregamos tu pedido.']);
});

test('no puede pagar con menos del total', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $respuesta = $this->post(route('checkout.store'), ['paga_con' => '99.99'] + $this->datos);

    // Entonces: el mensaje dice el total
    $respuesta->assertSessionHasErrors(['paga_con' => 'Debes pagar con al menos el total del pedido ($100.00).']);
    expect(Pedido::count())->toBe(0);
});

/* ---------- Radio de entrega ---------- */

test('no se entrega fuera del radio de la sucursal', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando: un punto a 3.02 km
    $respuesta = $this->post(route('checkout.store'), ['latitud' => '20.5571', 'longitud' => '-102.3306392'] + $this->datos);

    // Entonces
    $respuesta->assertSessionHasErrors([
        'latitud' => 'Tu ubicación está a 3.0 km y solo entregamos hasta 3 km. Puedes elegir recoger tu pedido en la sucursal.',
    ])->assertSessionHas('carrito', [$this->taco->id => 1]);
    expect(Pedido::count())->toBe(0);
    Mail::assertNothingSent();
});

test('se entrega hasta el borde del radio', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando: un punto a 2.99 km
    $respuesta = $this->post(route('checkout.store'), ['latitud' => '20.5569', 'longitud' => '-102.3306392'] + $this->datos);

    // Entonces
    $respuesta->assertSessionHasNoErrors();
    expect(Pedido::sole()->distancia_km)->toBe(2.99);
});

test('el radio se toma de la sucursal', function () {
    // Dado: la sucursal reduce su zona a 1 km
    $this->sucursal->update(['radio_entrega_km' => 1]);
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando: el punto de siempre, a 2.23 km
    $respuesta = $this->post(route('checkout.store'), $this->datos);

    // Entonces
    $respuesta->assertSessionHasErrors('latitud');
    expect(Pedido::count())->toBe(0);
});

test('el error de la zona de entrega se muestra en el formulario', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $respuesta = $this->from(route('checkout.create'))->followingRedirects()
        ->post(route('checkout.store'), ['latitud' => '20.6', 'longitud' => '-102.3306392'] + $this->datos);

    // Entonces: se ve el mensaje y la dirección sigue escrita
    $respuesta->assertOk()
        ->assertSee('Tu ubicación está a 7.8 km y solo entregamos hasta 3 km')
        ->assertSee('value="Av. Juárez"', false);
});

/* ---------- Confirmación y correo ---------- */

test('la confirmación muestra la dirección y el cambio', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);
    $this->post(route('checkout.store'), $this->datos);

    // Cuando
    $respuesta = $this->get(route('pedido.confirmacion', Pedido::sole()));

    // Entonces
    $respuesta->assertOk()
        ->assertSee('Entrega a domicilio')
        ->assertSee('Av. Juárez 120 int. 4, col. Centro')
        ->assertSee('Referencia: Portón verde')
        ->assertSee('Pagas con $150.00, tu cambio: $50.00')
        ->assertDontSee('Recoger en sucursal');
});

test('la confirmación sin "con cuánto paga" dice que paga con el monto exacto', function () {
    // Dado
    $this->withSession(['carrito' => [$this->taco->id => 1]]);
    $this->post(route('checkout.store'), ['paga_con' => ''] + $this->datos);

    // Cuando
    $respuesta = $this->get(route('pedido.confirmacion', Pedido::sole()));

    // Entonces
    $respuesta->assertOk()->assertSee('Pagas con el monto exacto.');
});

test('el correo al administrador trae la dirección, la ubicación y el cambio', function () {
    // Dado
    User::factory()->admin()->create();
    $this->withSession(['carrito' => [$this->taco->id => 1]]);

    // Cuando
    $this->post(route('checkout.store'), $this->datos);

    // Entonces: render() arma el HTML del correo para buscar texto en él
    Mail::assertSent(PedidoNuevo::class, function (PedidoNuevo $correo) {
        $html = $correo->render();

        return str_contains($html, 'Av. Juárez 120 int. 4, col. Centro')
            && str_contains($html, 'Portón verde')
            && str_contains($html, 'query=20.548,-102.34')
            && str_contains($html, '2.2 km en línea recta desde la sucursal')
            && str_contains($html, 'llevar $50.00 de cambio');
    });
});
