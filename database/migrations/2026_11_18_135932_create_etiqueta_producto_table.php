<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla pivote: singular y en orden alfabético (etiqueta + producto)
        Schema::create('etiqueta_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etiqueta_id')
                ->constrained('etiquetas')
                ->cascadeOnDelete();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            // Un producto no puede tener la misma etiqueta dos veces
            $table->unique(['etiqueta_id', 'producto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etiqueta_producto');
    }
};
