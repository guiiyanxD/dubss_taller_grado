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
        Schema::create('tipo_ocupacion_dependiente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_dependencia_eco');
            $table->string('nombre', 100);
            $table->string('archivo_adjuntar', 255)->nullable();
            $table->decimal('puntaje', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_dependencia_eco')
                  ->references('id')
                  ->on('dependencia_economica')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_ocupacion_dependiente');
    }
};
