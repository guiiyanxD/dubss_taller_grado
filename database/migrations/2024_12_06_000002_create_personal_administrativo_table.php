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
        Schema::create('personal_administrativo', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario')->primary();
            $table->string('cargo', 100);
            $table->string('departamento', 100);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_usuario')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_administrativo');
    }
};
