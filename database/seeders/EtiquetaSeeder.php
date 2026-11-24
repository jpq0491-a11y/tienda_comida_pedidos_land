<?php

namespace Database\Seeders;

use App\Models\Etiqueta;
use Illuminate\Database\Seeder;

class EtiquetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Catálogo fijo de etiquetas. Se usa Eloquent (no DB::table) como pide la rúbrica.
        // El modelo tiene $timestamps = false, así que no intenta llenar created_at ni updated_at.
        foreach (['Vegetariano', 'Picante', 'Sin gluten'] as $descripcion) {
            Etiqueta::create(['descripcion' => $descripcion]);
        }
    }
}
