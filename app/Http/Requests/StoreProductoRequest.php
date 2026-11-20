<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
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
            'categoria_id' => ['required', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'disponible' => ['boolean'],
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['integer', 'exists:etiquetas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'categoria_id.required' => 'Elige una categoría.',
            'categoria_id.exists' => 'La categoría elegida no existe.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'descripcion.max' => 'La descripción no puede tener más de 500 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio debe ser mayor a cero.',
            'precio.max' => 'El precio no puede ser mayor a 99,999.99.',
            'disponible.boolean' => 'El valor de "disponible" no es válido.',
            'etiquetas.array' => 'Las etiquetas no son válidas.',
            'etiquetas.*.integer' => 'Una de las etiquetas elegidas no es válida.',
            'etiquetas.*.exists' => 'Una de las etiquetas elegidas no existe.',
        ];
    }
}
