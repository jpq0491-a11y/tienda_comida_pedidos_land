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
            'latitud' => 19.4326,
            'longitud' => -99.1332,
            'radio_entrega_km' => 15,
        ]);
    }
}
