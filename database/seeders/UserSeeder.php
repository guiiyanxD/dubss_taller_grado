<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contraseña por defecto
        $password = Hash::make('password');

        // ============================================
        // SUPER ADMIN
        // ============================================
        $admin = User::create([
            'nombres' => 'Carlos',
            'apellidos' => 'Administrador',
            'ci' => '1000000',
            'email' => 'admin@dubss.edu',
            'password' => $password,
            'telefono' => '70000000',
            'ciudad' => 'La Paz',
            'fecha_nacimiento' => '1985-05-15',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('Super Admin');

        // ============================================
        // DPTO. SISTEMA (3 usuarios)
        // ============================================
        $sistema1 = User::create([
            'nombres' => 'María',
            'apellidos' => 'Quispe Mamani',
            'ci' => '2000001',
            'email' => 'sistema@dubss.edu',
            'password' => $password,
            'telefono' => '70000001',
            'ciudad' => 'La Paz',
            'fecha_nacimiento' => '1988-03-20',
            'email_verified_at' => now(),
        ]);
        $sistema1->assignRole('Dpto. Sistema');

        $sistema2 = User::create([
            'nombres' => 'Pedro',
            'apellidos' => 'Condori López',
            'ci' => '2000002',
            'email' => 'sistema2@dubss.edu',
            'password' => $password,
            'telefono' => '70000002',
            'ciudad' => 'La Paz',
            'fecha_nacimiento' => '1990-07-12',
            'email_verified_at' => now(),
        ]);
        $sistema2->assignRole('Dpto. Sistema');

        // ============================================
        // OPERADORES (2 usuarios)
        // ============================================
        $operador1 = User::create([
            'nombres' => 'Ana',
            'apellidos' => 'Flores Pinto',
            'ci' => '3000001',
            'email' => 'operador@dubss.edu',
            'password' => $password,
            'telefono' => '70000003',
            'ciudad' => 'La Paz',
            'fecha_nacimiento' => '1992-11-08',
            'email_verified_at' => now(),
        ]);
        $operador1->assignRole('Operador');

        $operador2 = User::create([
            'nombres' => 'Luis',
            'apellidos' => 'Vargas Ticona',
            'ci' => '3000002',
            'email' => 'operador2@dubss.edu',
            'password' => $password,
            'telefono' => '70000004',
            'ciudad' => 'La Paz',
            'fecha_nacimiento' => '1991-06-25',
            'email_verified_at' => now(),
        ]);
        $operador2->assignRole('Operador');

        // ============================================
        // DIRECCIÓN (1 usuario)
        // ============================================
        $direccion = User::create([
            'nombres' => 'Sandra',
            'apellidos' => 'Gutiérrez Rojas',
            'ci' => '4000001',
            'email' => 'direccion@dubss.edu',
            'password' => $password,
            'telefono' => '70000005',
            'ciudad' => 'La Paz',
            'fecha_nacimiento' => '1980-02-14',
            'email_verified_at' => now(),
        ]);
        $direccion->assignRole('Dirección');

        // ============================================
        // ESTUDIANTES (10 usuarios)
        // ============================================
        $estudiantesData = [
            ['Juan Carlos', 'Pérez López', '5000001', 'juan.perez@estudiante.edu', '70100001', 'La Paz', '2002-03-15'],
            ['María Elena', 'González Mamani', '5000002', 'maria.gonzalez@estudiante.edu', '70100002', 'Cochabamba', '2001-07-22'],
            ['José Luis', 'Ramírez Quispe', '5000003', 'jose.ramirez@estudiante.edu', '70100003', 'Santa Cruz', '2002-11-30'],
            ['Carmen Rosa', 'Flores Condori', '5000004', 'carmen.flores@estudiante.edu', '70100004', 'La Paz', '2003-01-18'],
            ['Diego Armando', 'Vargas Pinto', '5000005', 'diego.vargas@estudiante.edu', '70100005', 'Oruro', '2001-09-05'],
            ['Paola Andrea', 'Mamani Choque', '5000006', 'paola.mamani@estudiante.edu', '70100006', 'Potosí', '2002-04-12'],
            ['Roberto Carlos', 'Ticona López', '5000007', 'roberto.ticona@estudiante.edu', '70100007', 'La Paz', '2003-06-28'],
            ['Lucía Fernanda', 'Apaza Quispe', '5000008', 'lucia.apaza@estudiante.edu', '70100008', 'Tarija', '2001-12-10'],
            ['Miguel Ángel', 'Condori Mamani', '5000009', 'miguel.condori@estudiante.edu', '70100009', 'Beni', '2002-08-20'],
            ['Sofía Isabel', 'Rojas Flores', '5000010', 'sofia.rojas@estudiante.edu', '70100010', 'La Paz', '2003-02-25'],
        ];

        foreach ($estudiantesData as $index => $data) {
            $estudiante = User::create([
                'nombres' => $data[0],
                'apellidos' => $data[1],
                'ci' => $data[2],
                'email' => $data[3],
                'password' => $password,
                'telefono' => $data[4],
                'ciudad' => $data[5],
                'fecha_nacimiento' => $data[6],
                'email_verified_at' => now(),
            ]);
            $estudiante->assignRole('Estudiante');
        }

        $this->command->info('✅ Usuarios creados exitosamente');
        $this->command->info('');
        $this->command->table(
            ['Rol', 'Cantidad', 'Email Ejemplo', 'Password'],
            [
                ['Super Admin', '1', 'admin@dubss.edu', 'password'],
                ['Dpto. Sistema', '2', 'sistema@dubss.edu', 'password'],
                ['Operador', '2', 'operador@dubss.edu', 'password'],
                ['Dirección', '1', 'direccion@dubss.edu', 'password'],
                ['Estudiante', '10', 'juan.perez@estudiante.edu', 'password'],
                ['TOTAL', '16', '', ''],
            ]
        );
    }
}
