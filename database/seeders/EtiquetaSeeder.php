<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EtiquetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Catálogo fijo, se inserta con la fachada DB (sin timestamps)
        DB::table('etiquetas')->insert([
            ['descripcion' => 'Vegetariano'],
            ['descripcion' => 'Picante'],
            ['descripcion' => 'Sin gluten'],
        ]);
    }
}
