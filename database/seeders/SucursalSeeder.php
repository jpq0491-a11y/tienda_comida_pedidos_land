<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos de prueba: cambiar por los reales del negocio cuando estén definidos
        Sucursal::create([
            'nombre' => 'Sucursal Centro',
            'direccion' => 'Av. Principal 123, Centro',
            'telefono' => '5512345678',
            // Ayotlán, Jalisco (centro de la ciudad, tomado de Google Maps)
            'latitud' => 20.5299754,
            'longitud' => -102.3306392,
            'radio_entrega_km' => 3,
        ]);
    }
}
