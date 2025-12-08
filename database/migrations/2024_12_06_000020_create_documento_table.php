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
        Schema::create('documento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tramite');
            $table->string('tipo_documento', 100);
            $table->string('nombre_archivo', 255)->nullable();
            $table->string('ruta_digital', 500)->nullable();
            $table->string('estado_fisico', 50);
            $table->unsignedBigInteger('digitalizado_por')->nullable();
            $table->date('fecha_presentacion')->nullable();
            $table->date('fecha_digitalizacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->unsignedBigInteger('validado_por')->nullable();
            $table->timestamps();

            $table->foreign('id_tramite')
                  ->references('id')
                  ->on('tramite')
                  ->onDelete('cascade');
            $table->foreign('digitalizado_por')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            $table->foreign('validado_por')
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
        Schema::dropIfExists('documento');
    }
};
