<?php

namespace Database\Factories;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sucursal>
 */
class SucursalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Sucursal '.fake()->city(),
            'direccion' => fake()->streetAddress(),
            'telefono' => fake()->numerify('55########'),
            // Coordenadas cerca de Ayotlán, Jalisco (donde está la sucursal real)
            'latitud' => fake()->latitude(20.48, 20.58),
            'longitud' => fake()->longitude(-102.38, -102.28),
            'radio_entrega_km' => 3,
        ];
    }
}
