<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $estados = [
            ['nombre' => 'PENDIENTE', 'descripcion' => 'Trámite registrado, esperando validación'],
            ['nombre' => 'EN_VALIDACION', 'descripcion' => 'Documentación en proceso de validación'],
            ['nombre' => 'VALIDADO', 'descripcion' => 'Documentación validada correctamente'],
            ['nombre' => 'RECHAZADO', 'descripcion' => 'Documentación rechazada'],
            ['nombre' => 'EN_CLASIFICACION', 'descripcion' => 'En proceso de clasificación socioeconómica'],
            ['nombre' => 'CLASIFICADO', 'descripcion' => 'Clasificación completada'],
            ['nombre' => 'EN_DIGITALIZACION', 'descripcion' => 'Documentos en proceso de digitalización'],
            ['nombre' => 'DIGITALIZADO', 'descripcion' => 'Expediente digitalizado'],
            ['nombre' => 'APROBADO', 'descripcion' => 'Beca aprobada'],
            ['nombre' => 'DENEGADO', 'descripcion' => 'Beca denegada'],
        ];

        foreach ($estados as $estado) {
            DB::table('estado_tramite')->insert($estado);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('estado_tramite')->truncate();
    }
};
