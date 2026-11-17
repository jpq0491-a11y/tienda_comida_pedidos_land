<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'folio',
        'user_id',
        'sucursal_id',
        'nombre_cliente',
        'telefono',
        'tipo_entrega',
        'direccion',
        'latitud',
        'longitud',
        'distancia_km',
        'metodo_pago',
        'paga_con',
        'total',
        'estado',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'float',
            'longitud' => 'float',
            'distancia_km' => 'float',
            'paga_con' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }
}
