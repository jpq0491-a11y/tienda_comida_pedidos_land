<?php

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
| Pruebas del panel de pedidos del admin: lista (pendientes y realizados), detalle
| y cambio de estado. RefreshDatabase ya viene de tests/Pest.php.
*/

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->sucursal = Sucursal::factory()->create(['nombre' => 'Sucursal Centro']);
    $this->taco = Producto::factory()->create(['nombre' => 'Taco al pastor', 'precio' => 20]);
    $this->agua = Producto::factory()->create(['nombre' => 'Agua de jamaica', 'precio' => 25.50]);
});

/* ---------- Acceso ---------- */

test('un invitado es mandado al login', function (string $metodo, string $ruta) {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->create();

    // Cuando
    $respuesta = $this->call($metodo, route($ruta, $pedido), ['estado' => 'cancelado']);

    // Entonces
    $respuesta->assertRedirect(route('login'));
    expect($pedido->fresh()->estado)->toBe('pendiente');
})->with([
    'lista' => ['GET', 'admin.pedidos.index'],
    'detalle' => ['GET', 'admin.pedidos.show'],
    'cambiar estado' => ['PATCH', 'admin.pedidos.update'],
]);

test('un usuario que no es admin recibe 403', function (string $metodo, string $ruta) {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->create();

    // Cuando
    $respuesta = $this->actingAs(User::factory()->create())
        ->call($metodo, route($ruta, $pedido), ['estado' => 'cancelado']);

    // Entonces
    $respuesta->assertForbidden();
    expect($pedido->fresh()->estado)->toBe('pendiente');
})->with([
    'lista' => ['GET', 'admin.pedidos.index'],
    'detalle' => ['GET', 'admin.pedidos.show'],
    'cambiar estado' => ['PATCH', 'admin.pedidos.update'],
]);

/* ---------- Lista ---------- */

test('la lista separa los pedidos por atender de los realizados', function () {
    // Dado: un pedido en cada estado
    foreach (['pendiente', 'preparando', 'en_camino', 'entregado', 'cancelado'] as $estado) {
        Pedido::factory()->for($this->sucursal)->estado($estado)->create(['folio' => 'SC-'.strtoupper($estado)]);
    }

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.index'));

    // Entonces
    $respuesta->assertOk()
        ->assertViewHas('pendientes', fn ($pedidos) => $pedidos->pluck('folio')->sort()->values()->all()
            === ['SC-EN_CAMINO', 'SC-PENDIENTE', 'SC-PREPARANDO'])
        ->assertViewHas('realizados', fn ($pedidos) => $pedidos->pluck('folio')->sort()->values()->all()
            === ['SC-CANCELADO', 'SC-ENTREGADO']);
});

test('los pendientes salen del más viejo al más nuevo y los realizados al revés', function () {
    // Dado
    $this->travelTo(now()->subHours(2));
    $pendienteViejo = Pedido::factory()->for($this->sucursal)->create();
    $realizadoViejo = Pedido::factory()->for($this->sucursal)->estado('entregado')->create();
    $this->travelBack();
    $pendienteNuevo = Pedido::factory()->for($this->sucursal)->create();
    $realizadoNuevo = Pedido::factory()->for($this->sucursal)->estado('entregado')->create();

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.index'));

    // Entonces: el primero en llegar es el primero en atenderse
    $respuesta->assertSeeInOrder([$pendienteViejo->folio, $pendienteNuevo->folio, $realizadoNuevo->folio, $realizadoViejo->folio]);
});

test('la lista muestra los datos de cada pedido y el enlace a su detalle', function () {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->domicilio()->estado('en_camino')
        ->conProductos([$this->taco->id => 2, $this->agua->id => 1])
        ->create(['nombre_cliente' => 'Ana López']);

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.index'));

    // Entonces: 2 productos distintos (detalles_count) y el total 2 x 20 + 25.50
    $respuesta->assertOk()
        ->assertSee($pedido->folio)
        ->assertSee('Ana López')
        ->assertSee('Sucursal Centro')
        ->assertSee('Domicilio')
        ->assertSee('<td class="text-center">2</td>', false)
        ->assertSee('$65.50')
        ->assertSee('En camino')
        ->assertSee(route('admin.pedidos.show', $pedido));
});

test('la lista no hace más consultas cuando hay más pedidos (sin N+1)', function () {
    // Dado
    Pedido::factory()->for($this->sucursal)->conProductos([$this->taco->id => 1])->create();
    Pedido::factory()->for($this->sucursal)->estado('entregado')->conProductos([$this->taco->id => 1])->create();

    DB::enableQueryLog();
    $this->actingAs($this->admin)->get(route('admin.pedidos.index'))->assertOk();
    $consultasConPocos = count(DB::getQueryLog());

    // Cuando: 10 pedidos más, cada uno de otra sucursal
    Pedido::factory()->count(5)->conProductos([$this->taco->id => 1, $this->agua->id => 2])->create();
    Pedido::factory()->count(5)->estado('cancelado')->conProductos([$this->agua->id => 1])->create();

    DB::flushQueryLog();
    $this->actingAs($this->admin)->get(route('admin.pedidos.index'))->assertOk();

    // Entonces: with('sucursal') y withCount('detalles') no dependen del número de pedidos
    expect(count(DB::getQueryLog()))->toBe($consultasConPocos);
});

test('los realizados se paginan de 15 en 15', function () {
    // Dado
    Pedido::factory()->count(16)->for($this->sucursal)->estado('entregado')->create();

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.index'));

    // Entonces
    $respuesta->assertOk()
        ->assertViewHas('realizados', fn ($pedidos) => $pedidos->count() === 15 && $pedidos->total() === 16)
        ->assertSee('?page=2', false);
});

test('sin pedidos la lista muestra los mensajes de vacío', function () {
    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.index'));

    // Entonces
    $respuesta->assertOk()
        ->assertSee('No hay pedidos por atender.')
        ->assertSee('Todavía no hay pedidos realizados.');
});

/* ---------- Detalle ---------- */

test('el detalle de un pedido a domicilio muestra entrega, pago, teléfono y productos', function () {
    // Dado: 2 x 20 + 25.50 = 65.50, paga con 100
    $pedido = Pedido::factory()->for($this->sucursal)->domicilio()
        ->conProductos([$this->taco->id => 2, $this->agua->id => 1])
        ->create([
            'telefono' => '3451234567',
            'calle' => 'Av. Juárez',
            'numero_exterior' => '120',
            'numero_interior' => '4',
            'colonia' => 'Centro',
            'referencia' => 'Portón verde',
            'paga_con' => 100,
            'notas' => 'Sin cebolla',
        ]);

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.show', $pedido));

    // Entonces
    $respuesta->assertOk()
        ->assertSee($pedido->folio)
        ->assertSee('3451234567')
        ->assertSee('Av. Juárez 120 int. 4, col. Centro')
        ->assertSee('Portón verde')
        ->assertSee('https://www.google.com/maps/search/?api=1&query=20.548,-102.34', false)
        ->assertSee('2.2 km en línea recta')
        ->assertSee('Paga con $100.00')
        ->assertSee('Llevar $34.50 de cambio')
        ->assertSee('Sin cebolla')
        ->assertSeeInOrder(['Taco al pastor', '2', '$20.00', '$40.00'])
        ->assertSeeInOrder(['Agua de jamaica', '1', '$25.50', '$25.50'])
        ->assertSee('$65.50');
});

test('el detalle de un pedido para recoger no muestra dirección ni cambio', function () {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->conProductos([$this->taco->id => 1])->create();

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.show', $pedido));

    // Entonces
    $respuesta->assertOk()
        ->assertSee('Recoge en sucursal')
        ->assertDontSee('Dirección')
        ->assertDontSee('Google Maps')
        ->assertDontSee('de cambio');
});

test('el detalle sigue mostrando un producto que se borró del menú', function () {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->conProductos([$this->agua->id => 1])->create();
    $this->agua->delete();

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.show', $pedido));

    // Entonces
    $respuesta->assertOk()
        ->assertSee('Agua de jamaica')
        ->assertSee('Ya no está en el menú');
});

test('un pedido que no existe da 404', function () {
    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.show', 999));

    // Entonces
    $respuesta->assertNotFound();
});

/* ---------- Cambiar el estado ---------- */

test('el admin cambia el estado del pedido', function () {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->create(['folio' => 'SC-ABC12345']);

    // Cuando
    $respuesta = $this->actingAs($this->admin)
        ->patch(route('admin.pedidos.update', $pedido), ['estado' => 'en_camino']);

    // Entonces
    $respuesta->assertRedirect(route('admin.pedidos.show', $pedido))
        ->assertSessionHas('exito', 'El pedido SC-ABC12345 ahora está en camino.');
    $this->assertDatabaseHas('pedidos', ['id' => $pedido->id, 'estado' => 'en_camino']);
});

test('al entregarse el pedido pasa a realizados', function () {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->estado('en_camino')->create();

    // Cuando
    $this->actingAs($this->admin)->patch(route('admin.pedidos.update', $pedido), ['estado' => 'entregado']);

    // Entonces
    $this->get(route('admin.pedidos.index'))
        ->assertViewHas('pendientes', fn ($pedidos) => $pedidos->isEmpty())
        ->assertViewHas('realizados', fn ($pedidos) => $pedidos->first()->is($pedido));
});

test('el cliente ve el estado nuevo en su confirmación', function () {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->create();
    $this->actingAs($this->admin)->patch(route('admin.pedidos.update', $pedido), ['estado' => 'entregado']);
    auth()->logout();

    // Cuando
    $respuesta = $this->get(route('pedido.confirmacion', $pedido));

    // Entonces
    $respuesta->assertOk()->assertSee('<span class="badge text-bg-success">Entregado</span>', false);
});

test('el formulario del detalle tiene el estado actual seleccionado', function () {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->estado('preparando')->create();

    // Cuando
    $respuesta = $this->actingAs($this->admin)->get(route('admin.pedidos.show', $pedido));

    // Entonces
    $respuesta->assertOk()
        ->assertSee('action="'.route('admin.pedidos.update', $pedido).'"', false)
        ->assertSee('<option value="preparando" selected', false);
});

test('no se acepta un estado inválido', function (mixed $estado) {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->create();

    // Cuando
    $respuesta = $this->actingAs($this->admin)
        ->from(route('admin.pedidos.show', $pedido))
        ->patch(route('admin.pedidos.update', $pedido), ['estado' => $estado]);

    // Entonces
    $respuesta->assertRedirect(route('admin.pedidos.show', $pedido))->assertSessionHasErrors('estado');
    expect($pedido->fresh()->estado)->toBe('pendiente');
})->with([
    'vacío' => [''],
    'que no existe' => ['volando'],
    'con mayúsculas' => ['Entregado'],
    'un arreglo' => [['entregado']],
]);

test('al cambiar el estado no se modifican otros datos del pedido', function () {
    // Dado
    $pedido = Pedido::factory()->for($this->sucursal)->create(['total' => 100, 'telefono' => '3451234567']);

    // Cuando: mandan otros campos junto con el estado
    $this->actingAs($this->admin)->patch(route('admin.pedidos.update', $pedido), [
        'estado' => 'cancelado',
        'total' => 1,
        'telefono' => '0000000000',
    ]);

    // Entonces: solo cambió el estado
    expect($pedido->fresh())
        ->estado->toBe('cancelado')
        ->total->toBe('100.00')
        ->telefono->toBe('3451234567');
});
