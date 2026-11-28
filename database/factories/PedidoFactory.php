<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Pedido de invitado para recoger en sucursal, sin productos.
 * Pedido::factory()->domicilio()->conProductos([$taco->id => 2])->create()
 *
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Mismo formato que el folio del checkout (SC- y 8 letras o números)
            'folio' => 'SC-'.Str::upper(Str::random(8)),
            'sucursal_id' => Sucursal::factory(),
            'nombre_cliente' => fake()->name(),
            'telefono' => fake()->numerify('345#######'),
            'tipo_entrega' => 'recoger',
            'metodo_pago' => 'efectivo',
            'total' => fake()->randomFloat(2, 50, 500),
            'estado' => 'pendiente',
        ];
    }

    // Entrega a domicilio con dirección y ubicación cerca de la sucursal. paga_con queda null
    // (paga con el monto exacto): si se necesita, se pasa en create(['paga_con' => 300])
    public function domicilio(): static
    {
        return $this->state(fn (array $atributos) => [
            'tipo_entrega' => 'domicilio',
            'calle' => fake()->streetName(),
            'numero_exterior' => fake()->buildingNumber(),
            'colonia' => 'Centro',
            'referencia' => 'Portón verde',
            'latitud' => 20.5480000,
            'longitud' => -102.3400000,
            'distancia_km' => 2.23,
        ]);
    }

    public function estado(string $estado): static
    {
        return $this->state(['estado' => $estado]);
    }

    /**
     * Agrega los productos al pedido (en detalle_pedidos) y calcula el total.
     *
     * @param  array<int, int>  $cantidades  [producto_id => cantidad]
     */
    public function conProductos(array $cantidades): static
    {
        return $this->afterCreating(function (Pedido $pedido) use ($cantidades) {
            $total = 0;

            foreach (Producto::findMany(array_keys($cantidades)) as $producto) {
                $cantidad = $cantidades[$producto->id];
                $subtotal = $producto->precio * $cantidad;
                $total += $subtotal;

                $pedido->productos()->attach($producto->id, [
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                ]);
            }

            $pedido->update(['total' => $total]);
        });
    }
}
