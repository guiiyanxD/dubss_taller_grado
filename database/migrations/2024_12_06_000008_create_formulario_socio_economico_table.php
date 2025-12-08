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
        Schema::create('formulario_socio_economico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_estudiante');
            $table->boolean('validado_por')->default(false);
            $table->date('fecha_llenado');
            $table->boolean('completado')->default(false);
            $table->string('telefono_referencia', 15)->nullable();
            $table->text('comentario_personal')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('discapacidad')->default(false);
            $table->text('comentario_discapacidad')->nullable();
            $table->boolean('otro_beneficio')->default(false);
            $table->text('comentario_otro_beneficio')->nullable();
            $table->string('lugar_procedencia', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_estudiante')
                  ->references('id_usuario')
                  ->on('estudiante')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulario_socio_economico');
    }
};
