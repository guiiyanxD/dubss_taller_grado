<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================================
        // CREAR PERMISOS
        // ========================================

        // Permisos de Gestión de Becas
        Permission::create(['name' => 'gestionar_convocatorias']);
        Permission::create(['name' => 'crear_becas']);
        Permission::create(['name' => 'modificar_becas']);
        Permission::create(['name' => 'eliminar_becas']);
        Permission::create(['name' => 'consultar_becas']);

        // Permisos de Gestión de Trámites
        Permission::create(['name' => 'crear_postulacion']);
        Permission::create(['name' => 'validar_documentos']);
        Permission::create(['name' => 'digitalizar_documentos']);
        Permission::create(['name' => 'ejecutar_clasificacion']);
        Permission::create(['name' => 'asignar_resultados']);
        Permission::create(['name' => 'ver_tramite_propio']);
        Permission::create(['name' => 'ver_todos_tramites']);

        // Permisos de Gestión de Usuarios
        Permission::create(['name' => 'gestionar_usuarios_admin']);
        Permission::create(['name' => 'gestionar_roles']);
        Permission::create(['name' => 'ver_auditoria']);

        // Permisos de Formulario Socioeconómico
        Permission::create(['name' => 'llenar_formulario']);
        Permission::create(['name' => 'ver_formulario_propio']);
        Permission::create(['name' => 'ver_todos_formularios']);

        // Permisos de Business Intelligence
        Permission::create(['name' => 'ver_dashboards']);
        Permission::create(['name' => 'exportar_reportes']);
        Permission::create(['name' => 'ver_metricas_avanzadas']);

        // Permisos de Configuración del Sistema
        Permission::create(['name' => 'gestionar_catalogos']);
        Permission::create(['name' => 'configurar_pesos_clasificacion']);

        // ========================================
        // CREAR ROLES Y ASIGNAR PERMISOS
        // ========================================

        // ROL: ESTUDIANTE
        $rolEstudiante = Role::create(['name' => 'Estudiante']);
        $rolEstudiante->givePermissionTo([
            'crear_postulacion',
            'llenar_formulario',
            'ver_formulario_propio',
            'ver_tramite_propio',
        ]);

        // ROL: OPERADOR (Personal Administrativo - Recepción)
        $rolOperador = Role::create(['name' => 'Operador']);
        $rolOperador->givePermissionTo([
            'validar_documentos',
            'digitalizar_documentos',
            'ver_todos_tramites',
            'ver_todos_formularios',
            'consultar_becas',
        ]);

        // ROL: DPTO. SISTEMA (Personal Administrativo - Clasificación)
        $rolDptoSistema = Role::create(['name' => 'Dpto. Sistema']);
        $rolDptoSistema->givePermissionTo([
            'gestionar_convocatorias',
            'crear_becas',
            'modificar_becas',
            'eliminar_becas',
            'consultar_becas',
            'ejecutar_clasificacion',
            'asignar_resultados',
            'gestionar_usuarios_admin',
            'gestionar_roles',
            'gestionar_catalogos',
            'configurar_pesos_clasificacion',
            'ver_todos_tramites',
            'ver_todos_formularios',
            'ver_auditoria',
        ]);

        // ROL: DIRECCIÓN (Alta Dirección - Solo consulta y BI)
        $rolDireccion = Role::create(['name' => 'Dirección']);
        $rolDireccion->givePermissionTo([
            'consultar_becas',
            'ver_todos_tramites',
            'ver_dashboards',
            'exportar_reportes',
            'ver_metricas_avanzadas',
            'ver_auditoria',
        ]);

        // ROL: SUPER ADMIN (Para desarrollo/mantenimiento)
        $rolSuperAdmin = Role::create(['name' => 'Super Admin']);
        $rolSuperAdmin->givePermissionTo(Permission::all());

        $this->command->info('✅ Roles y permisos creados exitosamente');
        $this->command->info('');
        $this->command->info('📋 Roles creados:');
        $this->command->info('   - Estudiante (4 permisos)');
        $this->command->info('   - Operador (5 permisos)');
        $this->command->info('   - Dpto. Sistema (15 permisos)');
        $this->command->info('   - Dirección (6 permisos)');
        $this->command->info('   - Super Admin (todos los permisos)');
    }
}
