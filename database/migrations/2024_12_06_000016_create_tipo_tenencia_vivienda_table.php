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
        Schema::create('tipo_tenencia_vivienda', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tenencia_vivienda');
            $table->string('nombre', 100);
            $table->string('documento_adjuntar', 255)->nullable();
            $table->decimal('puntaje', 3, 1)->nullable();
            $table->timestamps();

            $table->foreign('id_tenencia_vivienda')
                  ->references('id')
                  ->on('tenencia_vivienda')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_tenencia_vivienda');
    }
};
