<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beca_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_beca')->constrained('beca');
            $table->foreignId('id_requisito')->constrained('requisito');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beca_requisito');
    }
};