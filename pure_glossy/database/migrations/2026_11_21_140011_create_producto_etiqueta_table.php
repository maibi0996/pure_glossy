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
        Schema::create('producto_etiqueta', function (Blueprint $table) {
            $table->bigInteger('id_producto_etiqueta', true);
            $table->bigInteger('id_producto');
            $table->bigInteger('id_etiqueta')->index('fk_etiquetas');

            $table->unique(['id_producto', 'id_etiqueta'], 'uq_producto_etiqueta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_etiqueta');
    }
};
