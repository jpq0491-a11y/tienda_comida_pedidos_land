<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;

/**
 * A dónde mandar al usuario después de iniciar sesión
 * (con contraseña o con el código de dos pasos).
 */
class LoginResponse implements LoginResponseContract, TwoFactorLoginResponseContract
{
    public function toResponse($request)
    {
        // La tienda y el panel están hechos en Blade, pero el formulario de login es Vue (Inertia).
        // Inertia::location hace que el navegador cargue la página completa y no la abra dentro de Vue.
        if ($request->user()->esAdmin()) {
            return Inertia::location(route('admin.inicio'));
        }

        // El cliente regresa a la página que quería ver (por ejemplo, Mis pedidos)
        // o, si no había ninguna, a la tienda ('home' en config/fortify.php)
        return Inertia::location(redirect()->intended(Fortify::redirects('login'))->getTargetUrl());
    }
}
