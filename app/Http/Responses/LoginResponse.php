<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    /**
     * A dónde mandar al usuario después de iniciar sesión.
     */
    public function toResponse($request)
    {
        // El panel admin está hecho en Blade, así que se usa Inertia::location
        // para que el navegador cargue la página completa y no la abra dentro de Vue.
        if ($request->user()->esAdmin()) {
            return Inertia::location(route('admin.inicio'));
        }

        // Cualquier otro usuario sigue el comportamiento normal de Fortify (/dashboard)
        return redirect()->intended(Fortify::redirects('login'));
    }
}
