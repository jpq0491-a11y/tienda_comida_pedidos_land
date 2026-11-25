<?php

namespace App\Services;

/**
 * Calcula la distancia en línea recta entre dos puntos del mapa con la fórmula de Haversine.
 *
 * La Tierra no es plana: entre dos coordenadas no se puede usar Pitágoras, hay que medir
 * sobre la superficie de una esfera. Haversine hace eso a partir de la latitud y la longitud.
 * Es distancia "en línea recta" (como vuela un pájaro), no por calles: el recorrido real
 * en moto suele ser un poco más largo.
 *
 * Está en su propia clase para poder probarla sola con un test unitario.
 */
class CalculadoraDistancia
{
    // Radio promedio de la Tierra
    private const RADIO_TIERRA_KM = 6371;

    public function kilometros(float $latitud1, float $longitud1, float $latitud2, float $longitud2): float
    {
        // Las funciones trigonométricas de PHP trabajan en radianes, no en grados
        $diferenciaLatitud = deg2rad($latitud2 - $latitud1);
        $diferenciaLongitud = deg2rad($longitud2 - $longitud1);

        $a = sin($diferenciaLatitud / 2) ** 2
            + cos(deg2rad($latitud1)) * cos(deg2rad($latitud2)) * sin($diferenciaLongitud / 2) ** 2;

        // Ángulo entre los dos puntos visto desde el centro de la Tierra
        $angulo = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::RADIO_TIERRA_KM * $angulo;
    }
}
