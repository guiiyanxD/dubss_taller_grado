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
        Schema::create('tramite', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_postulacion');
            $table->string('codigo', 50)->unique();
            $table->date('fecha_creacion');
            $table->string('clasificado', 2)->default('NO');
            $table->date('fecha_clasificacion')->nullable();
            $table->unsignedBigInteger('estado_actual');
            $table->timestamps();

            $table->foreign('id_postulacion')
                  ->references('id')
                  ->on('postulacion')
                  ->onDelete('cascade');
            $table->foreign('estado_actual')
                  ->references('id')
                  ->on('estado_tramite')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tramite');
    }
};
