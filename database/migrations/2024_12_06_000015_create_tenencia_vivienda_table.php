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
        Schema::create('tenencia_vivienda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_formulario');
            $table->string('tipo_tenencia', 50);
            $table->text('detalle_tenencia')->nullable();
            $table->decimal('puntaje', 3, 1)->nullable();
            $table->decimal('puntaje_total', 3, 1)->nullable();
            $table->timestamps();

            $table->foreign('id_formulario')
                  ->references('id')
                  ->on('formulario_socio_economico')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenencia_vivienda');
    }
};
