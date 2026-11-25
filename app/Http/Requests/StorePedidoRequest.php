<?php

namespace App\Http\Requests;

use App\Models\Sucursal;
use App\Services\CalculadoraDistancia;
use App\Services\Carrito;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Valida los datos del cliente al confirmar el pedido en el checkout.
 * Los productos y el total NO vienen del formulario: se toman del carrito en la sesión.
 */
class StorePedidoRequest extends FormRequest
{
    // Total del carrito; se guarda aquí para no consultarlo dos veces (en rules y en messages)
    private ?float $total = null;

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
            'tipo_entrega' => ['required', 'in:recoger,domicilio'],
            // exclude_unless: si recoge en sucursal, estos campos se descartan (no llegan a validated()),
            // aunque alguien los mande a mano
            'calle' => ['exclude_unless:tipo_entrega,domicilio', 'required', 'string', 'max:150'],
            'numero_exterior' => ['exclude_unless:tipo_entrega,domicilio', 'required', 'string', 'max:20'],
            'numero_interior' => ['exclude_unless:tipo_entrega,domicilio', 'nullable', 'string', 'max:20'],
            'colonia' => ['exclude_unless:tipo_entrega,domicilio', 'required', 'string', 'max:100'],
            'referencia' => ['exclude_unless:tipo_entrega,domicilio', 'nullable', 'string', 'max:255'],
            // Vienen de inputs hidden que llena el mapa
            'latitud' => ['exclude_unless:tipo_entrega,domicilio', 'required', 'numeric', 'between:-90,90'],
            'longitud' => ['exclude_unless:tipo_entrega,domicilio', 'required', 'numeric', 'between:-180,180'],
            // Opcional: vacío quiere decir que paga con el monto exacto
            'paga_con' => ['exclude_unless:tipo_entrega,domicilio', 'nullable', 'numeric', 'decimal:0,2', 'min:'.$this->total(), 'max:99999'],
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
            'tipo_entrega.required' => 'Elige si recoges en sucursal o te lo llevamos a domicilio.',
            'tipo_entrega.in' => 'Elige una forma de entrega válida.',
            'calle.required' => 'Escribe la calle.',
            'calle.max' => 'La calle no puede tener más de 150 caracteres.',
            'numero_exterior.required' => 'Escribe el número exterior (o S/N si no tiene).',
            'numero_exterior.max' => 'El número exterior no puede tener más de 20 caracteres.',
            'numero_interior.max' => 'El número interior no puede tener más de 20 caracteres.',
            'colonia.required' => 'Escribe la colonia.',
            'colonia.max' => 'La colonia no puede tener más de 100 caracteres.',
            'referencia.max' => 'La referencia no puede tener más de 255 caracteres.',
            // Un mismo mensaje para latitud y longitud: para el cliente es un solo dato (el punto en el mapa)
            'latitud.required' => 'Marca en el mapa dónde te entregamos tu pedido.',
            'latitud.numeric' => 'La ubicación del mapa no es válida. Vuelve a marcarla.',
            'latitud.between' => 'La ubicación del mapa no es válida. Vuelve a marcarla.',
            'longitud.required' => 'Marca en el mapa dónde te entregamos tu pedido.',
            'longitud.numeric' => 'La ubicación del mapa no es válida. Vuelve a marcarla.',
            'longitud.between' => 'La ubicación del mapa no es válida. Vuelve a marcarla.',
            'paga_con.numeric' => 'Escribe una cantidad válida, por ejemplo 200.',
            'paga_con.decimal' => 'La cantidad puede tener máximo 2 decimales.',
            'paga_con.min' => 'Debes pagar con al menos el total del pedido ($'.number_format($this->total(), 2).').',
            'paga_con.max' => 'La cantidad no puede ser mayor a $99,999.',
            'notas.max' => 'Las notas no pueden tener más de 500 caracteres.',
        ];
    }

    /**
     * Revisiones que se hacen después de las reglas de arriba.
     * Aquí: que la ubicación del mapa esté dentro del radio de entrega de la sucursal
     * (radio_entrega_km, hoy 3 km).
     * Se calcula en el servidor: el aviso del JS se puede saltar.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Solo con domicilio y si las coordenadas pasaron sus reglas (si no, ya hay un error)
                if ($this->input('tipo_entrega') !== 'domicilio'
                    || $validator->errors()->hasAny(['latitud', 'longitud'])) {
                    return;
                }

                $sucursal = Sucursal::firstOrFail();
                $kilometros = app(CalculadoraDistancia::class)->kilometros(
                    $sucursal->latitud, $sucursal->longitud, $this->input('latitud'), $this->input('longitud')
                );

                if ($kilometros > $sucursal->radio_entrega_km) {
                    $validator->errors()->add('latitud', 'Tu ubicación está a '.number_format($kilometros, 1)
                        .' km y solo entregamos hasta '.$sucursal->radio_entrega_km
                        .' km. Puedes elegir recoger tu pedido en la sucursal.');
                }
            },
        ];
    }

    private function total(): float
    {
        return $this->total ??= app(Carrito::class)->total();
    }
}
