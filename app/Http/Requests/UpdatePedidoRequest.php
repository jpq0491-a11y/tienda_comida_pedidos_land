<?php

namespace App\Http\Requests;

use App\Models\Pedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// El admin solo cambia el estado del pedido; los datos del cliente y los productos no se editan
class UpdatePedidoRequest extends FormRequest
{
    /**
     * El acceso ya lo controla el Gate 'administrar' en las rutas.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // El estado es una columna string: aquí se limita a los 5 valores válidos
            'estado' => ['required', 'string', Rule::in([...Pedido::ESTADOS_PENDIENTES, ...Pedido::ESTADOS_REALIZADOS])],
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'Elige el estado del pedido.',
            'estado.string' => 'El estado no es válido.',
            'estado.in' => 'El estado elegido no existe.',
        ];
    }
}
