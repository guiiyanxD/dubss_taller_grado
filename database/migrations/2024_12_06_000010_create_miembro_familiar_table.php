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
        Schema::create('miembro_familiar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_grupo_familiar');
            $table->string('nombre_completo', 255);
            $table->string('parentesco', 50);
            $table->integer('edad');
            $table->string('ocupacion', 100)->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('id_grupo_familiar')
                  ->references('id')
                  ->on('grupo_familiar')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miembro_familiar');
    }
};
