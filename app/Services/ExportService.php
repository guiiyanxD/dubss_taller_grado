<?php

namespace App\Services;

use App\Models\Postulacion;
use App\Models\Tramite;
use App\Models\Convocatoria;
use App\Models\Beca;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Servicio para exportar reportes en Excel y PDF
 *
 * CU-A4: Exportar Reportes
 */
class ExportService
{
    /**
     * Exportar ranking de becas a Excel
     */
    public function exportarRankingExcel(int $becaId): string
    {
        $beca = Beca::with('convocatoria')->findOrFail($becaId);

        // Obtener postulaciones ordenadas por puntaje
        $postulaciones = Postulacion::where('id_beca', $becaId)
            ->with(['estudiante', 'tramite.estadoActual'])
            ->orderBy('puntaje_final', 'desc')
            ->get();

        // Crear spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', "RANKING DE POSTULANTES - {$beca->nombre}");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        ]);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        // Subtítulo
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', "Convocatoria: {$beca->convocatoria->nombre} | Cupos: {$beca->cupos_disponibles} | Monto: Bs {$beca->monto}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Encabezados
        $headers = ['Posición', 'CI', 'Nombre Completo', 'Carrera', 'Puntaje', 'Estado Trámite', 'Resultado', 'Observaciones'];
        $sheet->fromArray($headers, null, 'A4');

        // Estilo de encabezados
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5496']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Datos
        $row = 5;
        $posicion = 1;
        $cuposDisponibles = $beca->cupos_disponibles;

        foreach ($postulaciones as $postulacion) {
            $resultado = $posicion <= $cuposDisponibles ? 'APROBADO' : 'DENEGADO';
            $estadoTramite = $postulacion->tramite ? $postulacion->tramite->estadoActual->nombre : 'SIN TRÁMITE';

            $data = [
                $posicion,
                $postulacion->estudiante->ci,
                $postulacion->estudiante->nombre_completo,
                $postulacion->estudiante->carrera,
                $postulacion->puntaje_final ?? 0,
                $estadoTramite,
                $resultado,
                $resultado === 'DENEGADO' ? 'Superó el cupo disponible' : 'Dentro del cupo',
            ];

            $sheet->fromArray($data, null, "A{$row}");

            // Colorear según resultado
            $colorFill = $resultado === 'APROBADO' ? 'C6EFCE' : 'FFC7CE';
            $sheet->getStyle("G{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorFill]],
                'font' => ['bold' => true],
            ]);

            // Línea de corte
            if ($posicion === $cuposDisponibles) {
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => 'FF0000']]],
                ]);
            }

            $row++;
            $posicion++;
        }

        // Bordes a toda la tabla
        $lastRow = $row - 1;
        $sheet->getStyle("A4:H{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Ajustar anchos
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(25);

        // Guardar archivo
        $fileName = 'ranking_' . \Str::slug($beca->nombre) . '_' . date('Y-m-d_His') . '.xlsx';
        $filePath = storage_path("app/public/exports/{$fileName}");

        // Crear directorio si no existe
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $fileName;
    }

    /**
     * Exportar estadísticas generales a Excel
     */
    public function exportarEstadisticasExcel(int $convocatoriaId): string
    {
        $convocatoria = Convocatoria::with('becas')->findOrFail($convocatoriaId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', "ESTADÍSTICAS GENERALES - {$convocatoria->nombre}");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        ]);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        // Resumen general
        $totalPostulaciones = Postulacion::where('id_convocatoria', $convocatoriaId)->count();
        $aprobadas = Postulacion::where('id_convocatoria', $convocatoriaId)->where('estado_postulado', 'APROBADO')->count();
        $denegadas = Postulacion::where('id_convocatoria', $convocatoriaId)->where('estado_postulado', 'DENEGADO')->count();
        $promedioPuntaje = Postulacion::where('id_convocatoria', $convocatoriaId)->avg('puntaje_final') ?? 0;

        $sheet->setCellValue('A3', 'RESUMEN GENERAL');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->setCellValue('A4', 'Total de Postulaciones:')->setCellValue('B4', $totalPostulaciones);
        $sheet->setCellValue('A5', 'Aprobadas:')->setCellValue('B5', $aprobadas);
        $sheet->setCellValue('A6', 'Denegadas:')->setCellValue('B6', $denegadas);
        $sheet->setCellValue('A7', 'Promedio de Puntaje:')->setCellValue('B7', number_format($promedioPuntaje, 2));

        // Estadísticas por beca
        $row = 9;
        $sheet->setCellValue("A{$row}", 'ESTADÍSTICAS POR BECA');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $headers = ['Beca', 'Cupos', 'Postulantes', 'Aprobados', 'Tasa Ocupación', 'Presupuesto'];
        $sheet->fromArray($headers, null, "A{$row}");
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5496']],
        ]);
        $row++;

        foreach ($convocatoria->becas as $beca) {
            $postulantes = Postulacion::where('id_beca', $beca->id)->count();
            $aprobados = Postulacion::where('id_beca', $beca->id)->where('estado_postulado', 'APROBADO')->count();
            $tasaOcupacion = $beca->cupos_disponibles > 0 ? ($aprobados / $beca->cupos_disponibles) * 100 : 0;
            $presupuesto = $aprobados * $beca->monto;

            $data = [
                $beca->nombre,
                $beca->cupos_disponibles,
                $postulantes,
                $aprobados,
                number_format($tasaOcupacion, 1) . '%',
                'Bs ' . number_format($presupuesto, 2),
            ];

            $sheet->fromArray($data, null, "A{$row}");
            $row++;
        }

        // Ajustar anchos
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);

        // Guardar
        $fileName = 'estadisticas_' . \Str::slug($convocatoria->nombre) . '_' . date('Y-m-d_His') . '.xlsx';
        $filePath = storage_path("app/public/exports/{$fileName}");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $fileName;
    }

    /**
     * Exportar ranking a PDF
     */
    public function exportarRankingPDF(int $becaId): string
    {
        $beca = Beca::with('convocatoria')->findOrFail($becaId);

        $postulaciones = Postulacion::where('id_beca', $becaId)
            ->with(['estudiante', 'tramite.estadoActual'])
            ->orderBy('puntaje_final', 'desc')
            ->get();

        $data = [
            'beca' => $beca,
            'postulaciones' => $postulaciones,
            'fecha' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('exports.ranking-pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        $fileName = 'ranking_' . \Str::slug($beca->nombre) . '_' . date('Y-m-d_His') . '.pdf';
        $filePath = storage_path("app/public/exports/{$fileName}");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $pdf->save($filePath);

        return $fileName;
    }

    /**
     * Exportar lista de estudiantes aprobados (para nómina de pago)
     */
    public function exportarNominaAprobados(int $convocatoriaId): string
    {
        $convocatoria = Convocatoria::findOrFail($convocatoriaId);

        $aprobados = Postulacion::where('id_convocatoria', $convocatoriaId)
            ->where('estado_postulado', 'APROBADO')
            ->with(['estudiante', 'beca'])
            ->orderBy('id_beca')
            ->orderBy('ranking')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', "NÓMINA DE BENEFICIARIOS - {$convocatoria->nombre}");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        ]);
        $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        // Encabezados
        $headers = ['N°', 'CI', 'Nombre Completo', 'Carrera', 'Beca', 'Monto (Bs)', 'Firma'];
        $sheet->fromArray($headers, null, 'A3');
        $sheet->getStyle('A3:G3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5496']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Datos
        $row = 4;
        $numero = 1;

        foreach ($aprobados as $postulacion) {
            $data = [
                $numero,
                $postulacion->estudiante->ci,
                $postulacion->estudiante->nombre_completo,
                $postulacion->estudiante->carrera,
                $postulacion->beca->nombre,
                number_format($postulacion->beca->monto, 2),
                '', // Columna para firma
            ];

            $sheet->fromArray($data, null, "A{$row}");
            $sheet->getRowDimension($row)->setRowHeight(25); // Altura para firma
            $row++;
            $numero++;
        }

        // Bordes
        $lastRow = $row - 1;
        $sheet->getStyle("A3:G{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Ajustar anchos
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(25);

        // Total
        $row++;
        $sheet->setCellValue("E{$row}", 'TOTAL:');
        $sheet->setCellValue("F{$row}", '=SUM(F4:F' . ($row - 1) . ')');
        $sheet->getStyle("E{$row}:F{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ]);

        // Guardar
        $fileName = 'nomina_aprobados_' . \Str::slug($convocatoria->nombre) . '_' . date('Y-m-d_His') . '.xlsx';
        $filePath = storage_path("app/public/exports/{$fileName}");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $fileName;
    }

    /**
     * Limpiar archivos de exportación antiguos (más de 7 días)
     */
    public function limpiarExportacionesAntiguas(): int
    {
        $directorio = storage_path('app/public/exports');

        if (!is_dir($directorio)) {
            return 0;
        }

        $archivos = glob($directorio . '/*');
        $eliminados = 0;
        $limiteTiempo = now()->subDays(7)->timestamp;

        foreach ($archivos as $archivo) {
            if (is_file($archivo) && filemtime($archivo) < $limiteTiempo) {
                unlink($archivo);
                $eliminados++;
            }
        }

        return $eliminados;
    }
}
