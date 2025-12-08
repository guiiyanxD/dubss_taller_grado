<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Estudiante;

class EstudianteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios con rol Estudiante
        $usuarios = User::role('Estudiante')->get();

        $carreras = [
            'Ingeniería de Sistemas',
            'Ingeniería Civil',
            'Medicina',
            'Derecho',
            'Administración de Empresas',
            'Economía',
            'Arquitectura',
            'Psicología',
            'Contabilidad',
            'Enfermería',
        ];

        foreach ($usuarios as $index => $usuario) {
            Estudiante::create([
                'id_usuario' => $usuario->id,
                'nro_registro' => '2024' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'carrera' => $carreras[$index % count($carreras)],
                'semestre' => rand(3, 8), // Semestres entre 3ro y 8vo
            ]);
        }

        $this->command->info('✅ Perfiles de estudiantes creados: ' . $usuarios->count());
    }
}
