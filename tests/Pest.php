<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Configuración de Pest
|--------------------------------------------------------------------------
|
| Todos los tests de Pest dentro de tests/Feature usan el TestCase de Laravel
| (para poder hacer peticiones, actingAs, etc.) y RefreshDatabase, que deja la
| base de datos de pruebas vacía antes de cada test.
|
| Los tests viejos escritos como clases de PHPUnit no se ven afectados: siguen
| declarando su propio "use RefreshDatabase".
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
