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
            // Coordenadas cerca del centro de la Ciudad de México
            'latitud' => fake()->latitude(19.30, 19.50),
            'longitud' => fake()->longitude(-99.25, -99.05),
            'radio_entrega_km' => 15,
        ];
    }
}
