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
        Schema::create('movimientos_cuenta_corriente', function (Blueprint $table) {
            $table->bigInteger('id_movimiento', true);
            $table->bigInteger('id_cliente')->index('idx_mov_cliente');
            $table->bigInteger('id_venta')->nullable()->index('idx_mov_venta');
            $table->enum('tipo', ['venta', 'pago', 'ajuste']);
            $table->string('concepto')->nullable();
            $table->decimal('monto', 10);
            $table->dateTime('fecha')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_cuenta_corriente');
    }
};
