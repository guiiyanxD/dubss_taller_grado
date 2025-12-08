<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PersonalAdministrativo;

class PersonalAdministrativoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Personal del Dpto. Sistema
        $usuariosSistema = User::role('Dpto. Sistema')->get();
        foreach ($usuariosSistema as $usuario) {
            PersonalAdministrativo::create([
                'id_usuario' => $usuario->id,
                'cargo' => 'Técnico de Sistemas',
                'departamento' => 'Departamento de Sistemas',
                'activo' => true,
            ]);
        }

        // Operadores
        $usuariosOperador = User::role('Operador')->get();
        foreach ($usuariosOperador as $usuario) {
            PersonalAdministrativo::create([
                'id_usuario' => $usuario->id,
                'cargo' => 'Operador de Ventanilla',
                'departamento' => 'Recepción y Validación',
                'activo' => true,
            ]);
        }

        // Dirección
        $usuariosDireccion = User::role('Dirección')->get();
        foreach ($usuariosDireccion as $usuario) {
            PersonalAdministrativo::create([
                'id_usuario' => $usuario->id,
                'cargo' => 'Director/a de Bienestar Social',
                'departamento' => 'Dirección',
                'activo' => true,
            ]);
        }

        $total = $usuariosSistema->count() + $usuariosOperador->count() + $usuariosDireccion->count();
        $this->command->info('✅ Perfiles de personal administrativo creados: ' . $total);
    }
}
