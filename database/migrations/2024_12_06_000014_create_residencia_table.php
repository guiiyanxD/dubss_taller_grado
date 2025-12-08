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
        Schema::create('residencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_formulario');
            $table->string('provincia', 100)->nullable();
            $table->string('zona', 100)->nullable();
            $table->string('calle', 255)->nullable();
            $table->integer('cant_banhos')->nullable();
            $table->integer('cant_salas')->nullable();
            $table->integer('cant_dormitorios')->nullable();
            $table->integer('cantt_comedor')->nullable();
            $table->string('barrio', 100)->nullable();
            $table->integer('cant_patios')->nullable();
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
        Schema::dropIfExists('residencia');
    }
};
