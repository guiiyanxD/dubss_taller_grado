<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beca', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('descripcion', 100);
            $table->string('codigo', 20);
            $table->string('version', 10);
            $table->string('periodo', 20);
            $table->foreignId('id_convocatoria')->constrained('convocatoria');
            $table->integer('cupos_disponibles');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beca');
    }
};