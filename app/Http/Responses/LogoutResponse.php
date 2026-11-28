<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

/**
 * A dónde mandar al usuario después de cerrar sesión.
 */
class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // El mensaje se muestra en la tienda con el flash 'exito' de siempre
        session()->flash('exito', 'Cerraste sesión. ¡Vuelve pronto!');

        // Se puede salir desde la navbar de la tienda (Blade) o desde la configuración de la cuenta (Vue).
        // Inertia::location sirve en los dos casos: desde Vue pide carga completa y desde Blade es una redirección normal.
        return Inertia::location(route('home'));
    }
}
