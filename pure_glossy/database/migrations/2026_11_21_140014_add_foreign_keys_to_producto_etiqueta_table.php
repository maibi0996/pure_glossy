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
        Schema::table('producto_etiqueta', function (Blueprint $table) {
            $table->foreign(['id_etiqueta'], 'fk_etiquetas')->references(['id_etiqueta'])->on('etiquetas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_producto'], 'fk_productos')->references(['id_producto'])->on('productos')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto_etiqueta', function (Blueprint $table) {
            $table->dropForeign('fk_etiquetas');
            $table->dropForeign('fk_productos');
        });
    }
};
