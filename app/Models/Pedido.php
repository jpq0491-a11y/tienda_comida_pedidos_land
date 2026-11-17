<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // Renglones del pedido (producto, cantidad, precio)
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Hoy siempre es null (pedidos de invitado); queda listo para cuentas de cliente
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
