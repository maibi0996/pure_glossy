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
        Schema::table('ventas_pagos', function (Blueprint $table) {
            $table->foreign(['id_metodo_pago'], 'fk_vp_metodo')->references(['id_metodo_pago'])->on('metodos_pago')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['id_venta'], 'fk_vp_venta')->references(['id_venta'])->on('ventas')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas_pagos', function (Blueprint $table) {
            $table->dropForeign('fk_vp_metodo');
            $table->dropForeign('fk_vp_venta');
        });
    }
};
