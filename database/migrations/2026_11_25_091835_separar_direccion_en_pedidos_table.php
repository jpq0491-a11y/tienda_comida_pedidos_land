<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cambia la dirección de entrega de una sola columna a sus partes (calle, número, colonia...),
 * así el repartidor y el admin ven cada dato por separado.
 * Todo es nullable porque solo se llena cuando la entrega es a domicilio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('direccion');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('calle', 150)->nullable()->after('tipo_entrega');
            $table->string('numero_exterior', 20)->nullable()->after('calle');
            $table->string('numero_interior', 20)->nullable()->after('numero_exterior');
            $table->string('colonia', 100)->nullable()->after('numero_interior');
            $table->string('referencia')->nullable()->after('colonia');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['calle', 'numero_exterior', 'numero_interior', 'colonia', 'referencia']);
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('tipo_entrega');
        });
    }
};
