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
        Schema::create('grupo_familiar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_formulario');
            $table->integer('cantidad_hijos')->default(0);
            $table->integer('cantidad_familiares');
            $table->boolean('tiene_hijos')->default(false);
            $table->decimal('puntaje', 5, 2)->nullable();
            $table->decimal('puntaje_total', 5, 2)->nullable();
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
        Schema::dropIfExists('grupo_familiar');
    }
};
