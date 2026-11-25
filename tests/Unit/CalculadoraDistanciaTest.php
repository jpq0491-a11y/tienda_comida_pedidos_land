<?php

use App\Services\CalculadoraDistancia;

/*
| Prueba unitaria: solo la fórmula de Haversine, sin base de datos ni peticiones.
| No usa el TestCase de Laravel (tests/Pest.php solo lo aplica en tests/Feature),
| por eso la clase se crea con new.
|
| toEqualWithDelta: con decimales no se compara exacto, se acepta una diferencia mínima.
*/

beforeEach(function () {
    $this->calculadora = new CalculadoraDistancia;
});

test('la distancia de un punto a sí mismo es cero', function () {
    // Cuando
    $kilometros = $this->calculadora->kilometros(20.5299754, -102.3306392, 20.5299754, -102.3306392);

    // Entonces
    expect($kilometros)->toBe(0.0);
});

test('un grado de latitud mide unos 111.19 km', function () {
    // Cuando: del ecuador a 1° al norte
    $kilometros = $this->calculadora->kilometros(0, 0, 1, 0);

    // Entonces: 2π × 6371 km / 360°
    expect($kilometros)->toEqualWithDelta(111.19, 0.01);
});

test('un grado de longitud en el ecuador mide lo mismo que uno de latitud', function () {
    // Cuando
    $kilometros = $this->calculadora->kilometros(0, 0, 0, 1);

    // Entonces
    expect($kilometros)->toEqualWithDelta(111.19, 0.01);
});

test('lejos del ecuador un grado de longitud mide menos', function () {
    // Cuando: a 60° de latitud los meridianos están más juntos (la mitad: cos 60° = 0.5)
    $kilometros = $this->calculadora->kilometros(60, 0, 60, 1);

    // Entonces
    expect($kilometros)->toEqualWithDelta(55.6, 0.1);
});

test('da lo mismo medir de A a B que de B a A', function () {
    // Cuando
    $ida = $this->calculadora->kilometros(20.5299754, -102.3306392, 20.5480, -102.3400);
    $vuelta = $this->calculadora->kilometros(20.5480, -102.3400, 20.5299754, -102.3306392);

    // Entonces
    expect($ida)->toEqualWithDelta($vuelta, 0.000001);
});

test('calcula la distancia entre dos puntos reales', function (float $latitud, float $longitud, float $esperado) {
    // Dado: la sucursal en Ayotlán, Jalisco
    $sucursal = [20.5299754, -102.3306392];

    // Cuando
    $kilometros = $this->calculadora->kilometros($sucursal[0], $sucursal[1], $latitud, $longitud);

    // Entonces
    expect($kilometros)->toEqualWithDelta($esperado, 0.01);
})->with([
    'un punto dentro de la colonia' => [20.5480, -102.3400, 2.23],
    'casi en el borde de los 3 km' => [20.5569, -102.3306392, 2.99],
    'justo afuera de los 3 km' => [20.5571, -102.3306392, 3.02],
    'el Zócalo de la Ciudad de México' => [19.4326, -99.1332, 355.71],
]);

test('al cruzar la línea de cambio de fecha toma el camino corto', function () {
    // Cuando: 179.5° este y 179.5° oeste están a solo 1° de distancia, no a 359°
    $kilometros = $this->calculadora->kilometros(0, 179.5, 0, -179.5);

    // Entonces
    expect($kilometros)->toEqualWithDelta(111.19, 0.01);
});
