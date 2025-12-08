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
        Schema::create('notificacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_estudiante');
            $table->unsignedBigInteger('id_tramite')->nullable();
            $table->string('tipo', 50);
            $table->string('titulo', 255);
            $table->text('mensaje');
            $table->boolean('leido')->default(false);
            $table->date('fecha_creacion');
            $table->date('fecha_lectura')->nullable();
            $table->string('canal', 50);
            $table->timestamps();

            $table->foreign('id_estudiante')
                  ->references('id_usuario')
                  ->on('estudiante')
                  ->onDelete('cascade');
            $table->foreign('id_tramite')
                  ->references('id')
                  ->on('tramite')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacion');
    }
};
