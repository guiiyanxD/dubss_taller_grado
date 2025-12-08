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
        Schema::create('postulacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_estudiante');
            $table->unsignedBigInteger('id_convocatoria');
            $table->unsignedBigInteger('id_formulario');
            $table->unsignedBigInteger('id_beca');
            $table->date('fecha_postulacion');
            $table->string('estado_postulado', 50);
            $table->text('motivo_rechazo')->nullable();
            $table->integer('posicion_ranking')->nullable();
            $table->decimal('puntaje_final', 5, 2)->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();

            $table->foreign('id_estudiante')
                  ->references('id_usuario')
                  ->on('estudiante')
                  ->onDelete('cascade');
            $table->foreign('id_convocatoria')
                  ->references('id')
                  ->on('convocatoria')
                  ->onDelete('cascade');
            $table->foreign('id_formulario')
                  ->references('id')
                  ->on('formulario_socio_economico')
                  ->onDelete('cascade');
            $table->foreign('id_beca')
                  ->references('id')
                  ->on('beca')
                  ->onDelete('cascade');
            $table->foreign('creado_por')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulacion');
    }
};
