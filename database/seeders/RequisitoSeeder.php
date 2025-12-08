<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Requisito;

class RequisitoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requisitos = [
            [
                'nombre' => 'Formulario Socioeconómico',
                'descripcion' => 'Formulario completo y firmado',
            ],
            [
                'nombre' => 'Fotocopia CI',
                'descripcion' => 'Fotocopia de cédula de identidad vigente',
            ],
            [
                'nombre' => 'Kardex Actualizado',
                'descripcion' => 'Kardex actualizado del semestre en curso',
            ],
            [
                'nombre' => 'Comprobante de Domicilio',
                'descripcion' => 'Factura de luz, agua o teléfono del último mes',
            ],
            [
                'nombre' => 'Fotografía',
                'descripcion' => 'Fotografía tamaño carnet reciente',
            ],
            [
                'nombre' => 'Carta de Solicitud',
                'descripcion' => 'Carta dirigida al Director de Bienestar Social',
            ],
            [
                'nombre' => 'Certificado de Nacimiento',
                'descripcion' => 'Certificado de nacimiento original',
            ],
            [
                'nombre' => 'Declaración Jurada',
                'descripcion' => 'Declaración jurada de ingresos familiares',
            ],
        ];

        foreach ($requisitos as $requisito) {
            Requisito::create($requisito);
        }

        $this->command->info('✅ Requisitos creados: ' . count($requisitos));
    }
}
