<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // No se repiten nombres entre productos activos; uno borrado (soft delete) no cuenta,
            // así se puede volver a crear un producto con el nombre de uno eliminado
            'nombre' => ['required', 'string', 'max:100', Rule::unique('productos', 'nombre')->withoutTrashed()],
            'descripcion' => ['nullable', 'string', 'max:500'],
            // decimal:0,2 = de 0 a 2 decimales; evita que 25.999 se redondee sin avisar
            'precio' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999.99'],
            'disponible' => ['boolean'],
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['integer', 'exists:etiquetas,id'],
            // Seguridad: image y mimes revisan el CONTENIDO real del archivo; extensions revisa el NOMBRE.
            // Juntas rechazan un .php aunque venga renombrado (virus.php -> virus.jpg) o disfrazado
            // (contenido de imagen con nombre foto.php). Sin svg: puede llevar JavaScript.
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'categoria_id.required' => 'Elige una categoría.',
            'categoria_id.exists' => 'La categoría elegida no existe.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.unique' => 'Ya existe un producto con ese nombre.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede tener más de 500 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.decimal' => 'El precio puede tener máximo 2 decimales.',
            'precio.min' => 'El precio debe ser mayor a cero.',
            'precio.max' => 'El precio no puede ser mayor a 99,999.99.',
            'disponible.boolean' => 'El valor de "disponible" no es válido.',
            'etiquetas.array' => 'Las etiquetas no son válidas.',
            'etiquetas.*.integer' => 'Una de las etiquetas elegidas no es válida.',
            'etiquetas.*.exists' => 'Una de las etiquetas elegidas no existe.',
            'imagen.uploaded' => 'La imagen no se pudo subir; revisa que no pese más de 2 MB e inténtalo de nuevo.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
            'imagen.extensions' => 'La imagen debe tener extensión .jpg, .jpeg, .png o .webp.',
            'imagen.max' => 'La imagen no puede pesar más de 2 MB.',
        ];
    }
}
