<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    // Laravel pluralizaría "Sucursal" como "sucursals", por eso se indica el nombre de la tabla
    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'latitud',
        'longitud',
        'radio_entrega_km',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'float',
            'longitud' => 'float',
            'radio_entrega_km' => 'float',
        ];
    }
}
