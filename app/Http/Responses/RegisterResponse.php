<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;

/**
 * A dónde mandar al cliente después de crear su cuenta.
 */
class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        // Igual que en LoginResponse: carga completa de la página porque la tienda es Blade
        return Inertia::location(redirect()->intended(Fortify::redirects('register'))->getTargetUrl());
    }
}
