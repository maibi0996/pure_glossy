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
        Schema::table('movimientos_cuenta_corriente', function (Blueprint $table) {
            $table->foreign(['id_cliente'], 'fk_cliente')->references(['id_cliente'])->on('clientes_corrientes')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['id_venta'], 'fk_ventas')->references(['id_venta'])->on('ventas')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_cuenta_corriente', function (Blueprint $table) {
            $table->dropForeign('fk_cliente');
            $table->dropForeign('fk_ventas');
        });
    }
};
