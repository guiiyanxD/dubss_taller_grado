<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  🌱 INICIANDO SEEDERS DE DUBSS');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');

        $this->call([
            // 1. Roles y permisos (PRIMERO)
            RolePermissionSeeder::class,
            
            // 2. Usuarios base
            UserSeeder::class,
            
            // 3. Perfiles especializados
            EstudianteSeeder::class,
            PersonalAdministrativoSeeder::class,
            
            // 4. Catálogos base (ya vienen del SQL pero por si acaso)
            // EstadoTramiteSeeder ya está en el SQL adaptado
            
            // 5. Convocatorias y becas
            ConvocatoriaSeeder::class,
            RequisitoSeeder::class,
            BecaSeeder::class,
            
            // 6. Formularios socioeconómicos
            FormularioSocioEconomicoSeeder::class,
            
            // 7. Postulaciones
            PostulacionSeeder::class,
            
            // 8. Trámites (con documentos y notificaciones)
            TramiteSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  ✅ SEEDERS COMPLETADOS EXITOSAMENTE');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
        
        // Resumen estadístico
        $this->command->info('📊 RESUMEN DE DATOS GENERADOS:');
        $this->command->info('');
        
        $stats = [
            ['Modelo', 'Cantidad'],
            ['─────────────────────────────', '────────'],
            ['Usuarios', \App\Models\User::count()],
            ['Estudiantes', \App\Models\Estudiante::count()],
            ['Personal Administrativo', \App\Models\PersonalAdministrativo::count()],
            ['Convocatorias', \App\Models\Convocatoria::count()],
            ['Becas', \App\Models\Beca::count()],
            ['Requisitos', \App\Models\Requisito::count()],
            ['Formularios', \App\Models\FormularioSocioEconomico::count()],
            ['Grupos Familiares', \App\Models\GrupoFamiliar::count()],
            ['Miembros Familiares', \App\Models\MiembroFamiliar::count()],
            ['Postulaciones', \App\Models\Postulacion::count()],
            ['Trámites', \App\Models\Tramite::count()],
            ['Historial Trámites', \App\Models\TramiteHistorial::count()],
            ['Documentos', \App\Models\Documento::count()],
            ['Notificaciones', \App\Models\Notificacion::count()],
        ];
        
        $this->command->table($stats[0], array_slice($stats, 1));
        
        $this->command->info('');
        $this->command->info('🔑 CREDENCIALES DE ACCESO:');
        $this->command->info('');
        $this->command->info('   Super Admin:    admin@dubss.edu / password');
        $this->command->info('   Dpto. Sistema:  sistema@dubss.edu / password');
        $this->command->info('   Operador:       operador@dubss.edu / password');
        $this->command->info('   Dirección:      direccion@dubss.edu / password');
        $this->command->info('   Estudiante:     juan.perez@estudiante.edu / password');
        $this->command->info('');
        $this->command->info('💡 TIP: Todos los usuarios tienen la contraseña "password"');
        $this->command->info('');
    }
}
