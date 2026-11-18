<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(), // crea una categoría si no se indica otra
            'nombre' => fake()->randomElement([
                'Taco al pastor', 'Taco de bistec', 'Torta de milanesa', 'Quesadilla',
                'Agua de horchata', 'Refresco', 'Flan', 'Enchiladas verdes',
            ]),
            'descripcion' => fake()->sentence(),
            'precio' => fake()->randomFloat(2, 15, 150),
            'imagen' => null, // la imagen se sube en el CRUD del admin
            'disponible' => true,
        ];
    }

    // Estado: producto que el admin marcó como no disponible
    public function noDisponible(): static
    {
        return $this->state(fn (array $attributes) => [
            'disponible' => false,
        ]);
    }
}
