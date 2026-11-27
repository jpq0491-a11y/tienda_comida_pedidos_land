<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    // Estados que la tienda todavía tiene que atender
    public const ESTADOS_PENDIENTES = ['pendiente', 'preparando', 'en_camino'];

    // Estados finales: el pedido ya se cerró
    public const ESTADOS_REALIZADOS = ['entregado', 'cancelado'];

    protected $fillable = [
        'folio',
        'user_id',
        'sucursal_id',
        'nombre_cliente',
        'telefono',
        'tipo_entrega',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'referencia',
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

    /**
     * Cambio que hay que llevarle al cliente: $pedido->cambio.
     * null si no dijo con cuánto paga (paga con el monto exacto o recoge en sucursal).
     */
    protected function cambio(): Attribute
    {
        return Attribute::get(fn () => $this->paga_con === null
            ? null
            : round($this->paga_con - $this->total, 2));
    }

    /**
     * Dirección armada en una línea: $pedido->direccion_completa,
     * por ejemplo "Av. Juárez 120 int. 4, col. Centro". La referencia se muestra aparte.
     */
    protected function direccionCompleta(): Attribute
    {
        return Attribute::get(fn () => $this->calle === null
            ? null
            : $this->calle.' '.$this->numero_exterior
                .($this->numero_interior ? ' int. '.$this->numero_interior : '')
                .', col. '.$this->colonia);
    }

    // Scope local: Pedido::pendientes()->get()
    #[Scope]
    protected function pendientes(Builder $query): void
    {
        $query->whereIn('estado', self::ESTADOS_PENDIENTES);
    }

    // Scope local: Pedido::realizados()->get()
    #[Scope]
    protected function realizados(Builder $query): void
    {
        $query->whereIn('estado', self::ESTADOS_REALIZADOS);
    }

    // Renglones del pedido (producto, cantidad, precio)
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    // Productos del pedido (M:N) usando detalle_pedidos como tabla pivote
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'detalle_pedidos') // nombre fuera de convención
            ->withPivot('cantidad', 'precio_unitario', 'subtotal')
            ->withTimestamps()
            ->withTrashed(); // para que los pedidos viejos sigan mostrando productos borrados
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
