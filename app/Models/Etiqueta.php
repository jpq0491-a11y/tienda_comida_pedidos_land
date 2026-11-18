<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Etiqueta extends Model
{
    // La tabla no tiene created_at ni updated_at (es un catálogo)
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
    ];

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class);
    }
}
