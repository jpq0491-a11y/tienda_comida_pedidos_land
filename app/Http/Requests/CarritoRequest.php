<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida la cantidad al agregar un producto al carrito o al cambiarla.
 */
class CarritoRequest extends FormRequest
{
    /**
     * El carrito es de invitado: cualquiera puede usarlo.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cantidad' => ['required', 'integer', 'min:1', 'max:'.config('tienda.maximo_por_producto')],
        ];
    }

    public function messages(): array
    {
        return [
            'cantidad.required' => 'Indica cuántos quieres.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad mínima es 1.',
            'cantidad.max' => 'Puedes pedir máximo :max piezas de cada producto.',
        ];
    }
}
