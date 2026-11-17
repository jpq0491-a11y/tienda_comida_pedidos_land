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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            // Hoy los pedidos son de invitados (null); queda listo para cuentas de cliente
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->restrictOnDelete();

            // Datos del cliente
            $table->string('nombre_cliente');
            $table->string('telefono', 20);

            // Entrega: recoger | domicilio
            $table->string('tipo_entrega', 20);
            // Solo se llenan si la entrega es a domicilio
            $table->string('direccion')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->decimal('distancia_km', 5, 2)->nullable();

            // Pago (solo efectivo)
            $table->string('metodo_pago', 20)->default('efectivo');
            // Con cuánto paga el cliente, para calcular el cambio
            $table->decimal('paga_con', 8, 2)->nullable();
            $table->decimal('total', 8, 2);

            // pendiente | preparando | en_camino | entregado | cancelado
            $table->string('estado', 20)->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
