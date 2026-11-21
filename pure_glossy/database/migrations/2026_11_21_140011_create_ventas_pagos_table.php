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
        Schema::create('ventas_pagos', function (Blueprint $table) {
            $table->bigInteger('id_venta_pago', true);
            $table->bigInteger('id_venta')->index('idx_vp_venta');
            $table->bigInteger('id_metodo_pago')->index('idx_vp_metodo');
            $table->decimal('monto', 10);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_pagos');
    }
};
