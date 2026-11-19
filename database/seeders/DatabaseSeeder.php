<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Administrador para entrar al panel (contraseña: password).
        // 'rol' no está en fillable, pero la factory sí lo deja asignar.
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@tienda.test',
            'rol' => 'admin',
        ]);

        // EtiquetaSeeder va antes que MenuSeeder porque el menú usa las etiquetas
        $this->call([
            SucursalSeeder::class,
            EtiquetaSeeder::class,
            MenuSeeder::class,
        ]);
    }
}
