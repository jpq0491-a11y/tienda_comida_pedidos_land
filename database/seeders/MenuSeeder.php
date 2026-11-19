<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Etiqueta;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ids de las etiquetas (las crea EtiquetaSeeder, que corre antes)
        $vegetariano = Etiqueta::where('descripcion', 'Vegetariano')->first()->id;
        $picante = Etiqueta::where('descripcion', 'Picante')->first()->id;
        $sinGluten = Etiqueta::where('descripcion', 'Sin gluten')->first()->id;

        // Cada categoría con sus productos; 'etiquetas' es la lista que se asigna con attach
        $menu = [
            ['nombre' => 'Tacos', 'productos' => [
                ['nombre' => 'Taco al pastor', 'descripcion' => 'Carne al pastor con piña, cebolla y cilantro.', 'precio' => 22, 'etiquetas' => [$picante, $sinGluten]],
                ['nombre' => 'Taco de bistec', 'descripcion' => 'Bistec de res con cebolla asada.', 'precio' => 25, 'etiquetas' => [$sinGluten]],
                ['nombre' => 'Taco de nopal', 'descripcion' => 'Nopales asados con queso fresco.', 'precio' => 20, 'etiquetas' => [$vegetariano, $sinGluten]],
            ]],
            ['nombre' => 'Tortas', 'productos' => [
                ['nombre' => 'Torta de milanesa', 'descripcion' => 'Milanesa de res, aguacate, jitomate y frijoles.', 'precio' => 65, 'etiquetas' => []],
                ['nombre' => 'Torta ahogada', 'descripcion' => 'Carnitas bañadas en salsa de chile de árbol.', 'precio' => 70, 'etiquetas' => [$picante]],
                ['nombre' => 'Torta de panela', 'descripcion' => 'Queso panela asado con aguacate y chipotle.', 'precio' => 55, 'etiquetas' => [$vegetariano]],
            ]],
            ['nombre' => 'Bebidas', 'productos' => [
                ['nombre' => 'Agua de horchata', 'descripcion' => 'Vaso de 500 ml.', 'precio' => 25, 'etiquetas' => [$vegetariano]],
                ['nombre' => 'Agua de jamaica', 'descripcion' => 'Vaso de 500 ml.', 'precio' => 25, 'etiquetas' => [$vegetariano, $sinGluten]],
                ['nombre' => 'Refresco', 'descripcion' => 'Lata de 355 ml.', 'precio' => 20, 'etiquetas' => [$sinGluten]],
            ]],
            ['nombre' => 'Postres', 'productos' => [
                ['nombre' => 'Flan napolitano', 'descripcion' => 'Rebanada de flan casero.', 'precio' => 35, 'etiquetas' => [$vegetariano, $sinGluten]],
                ['nombre' => 'Churros', 'descripcion' => 'Orden de 4 churros con azúcar y canela.', 'precio' => 40, 'etiquetas' => [$vegetariano]],
            ]],
        ];

        foreach ($menu as $orden => $datosCategoria) {
            $categoria = Categoria::create([
                'nombre' => $datosCategoria['nombre'],
                'orden' => $orden + 1,
            ]);

            foreach ($datosCategoria['productos'] as $datosProducto) {
                // Crea el producto ya ligado a la categoría (llena categoria_id solo)
                $producto = $categoria->productos()->create([
                    'nombre' => $datosProducto['nombre'],
                    'descripcion' => $datosProducto['descripcion'],
                    'precio' => $datosProducto['precio'],
                    'disponible' => true,
                ]);

                $producto->etiquetas()->attach($datosProducto['etiquetas']);
            }
        }
    }
}
