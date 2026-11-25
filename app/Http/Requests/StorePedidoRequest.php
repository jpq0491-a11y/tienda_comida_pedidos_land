<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida los datos del cliente al confirmar el pedido en el checkout.
 * Los productos y el total NO vienen del formulario: se toman del carrito en la sesión.
 */
class StorePedidoRequest extends FormRequest
{
    /**
     * El checkout es público: cualquier invitado puede hacer un pedido.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_cliente' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'digits:10'],
            'notas' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_cliente.required' => 'Escribe tu nombre.',
            'nombre_cliente.max' => 'El nombre no puede tener más de 100 caracteres.',
            'telefono.required' => 'Escribe tu teléfono.',
            'telefono.digits' => 'El teléfono debe tener exactamente 10 dígitos, sin espacios ni guiones.',
            'notas.max' => 'Las notas no pueden tener más de 500 caracteres.',
        ];
    }
}
