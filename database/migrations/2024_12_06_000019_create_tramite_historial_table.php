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
        Schema::create('tramite_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tramite');
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('revisador_por')->nullable();
            $table->date('fecha_revision');
            $table->string('estado_anterior', 50)->nullable();
            $table->string('estado_nuevo', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_tramite')
                  ->references('id')
                  ->on('tramite')
                  ->onDelete('cascade');
            $table->foreign('revisador_por')
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
        Schema::dropIfExists('tramite_historial');
    }
};
