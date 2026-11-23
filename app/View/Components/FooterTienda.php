<?php

namespace App\View\Components;

use App\Models\Sucursal;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Componente de clase (no anónimo) porque necesita consultar la base de datos:
 * así la consulta vive aquí y no en cada vista que use el footer.
 */
class FooterTienda extends Component
{
    // Las propiedades públicas quedan disponibles en la vista del componente
    public ?Sucursal $sucursal;

    public array $horario;

    public function __construct()
    {
        // Por ahora el negocio tiene una sola sucursal; first() devuelve null si no hay
        $this->sucursal = Sucursal::first();
        $this->horario = config('tienda.horario', []);
    }

    public function render(): View|Closure|string
    {
        return view('components.footer-tienda');
    }
}
