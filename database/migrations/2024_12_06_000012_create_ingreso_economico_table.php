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
        Schema::create('ingreso_economico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_dependencia_eco');
            $table->string('rango_monto', 50);
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
        Schema::dropIfExists('ingreso_economico');
    }

};
