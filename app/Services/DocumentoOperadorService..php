<?php

namespace App\Services;

use App\Models\Tramite;
use App\Models\Documento;
use App\Models\EstadoTramite;
use App\Models\Notificacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DocumentoOperadorService
{
    /**
     * Obtener trámite para digitalización
     */
    public function obtenerParaDigitalizacion(int $tramiteId): ?array
    {
        $tramite = Tramite::with([
            'postulacion.estudiante.user',
            'postulacion.beca',
            'estadoActual',
            'documentos',
        ])->find($tramiteId);

        if (!$tramite) {
            return null;
        }

        // Verificar que esté en estado VALIDADO
        if ($tramite->estadoActual->nombre !== 'VALIDADO') {
            return null;
        }

        return [
            'id' => $tramite->id,
            'codigo' => $tramite->codigo,
            'estudiante' => [
                'nombre' => $tramite->postulacion->estudiante->user->name,
                'ci' => $tramite->postulacion->estudiante->user->ci,
            ],
            'beca' => [
                'nombre' => $tramite->postulacion->beca->nombre,
            ],
            'documentos_requeridos' => [
                ['tipo' => 'CI', 'nombre' => 'Cédula de Identidad', 'obligatorio' => true],
                ['tipo' => 'KARDEX', 'nombre' => 'Kardex Académico', 'obligatorio' => true],
                ['tipo' => 'COMPROBANTE_DOMICILIO', 'nombre' => 'Comprobante de Domicilio', 'obligatorio' => true],
                ['tipo' => 'CERTIFICADO_INGRESOS', 'nombre' => 'Certificado de Ingresos', 'obligatorio' => false],
            ],
            'documentos_digitalizados' => $tramite->documentos->map(fn($d) => [
                'id' => $d->id,
                'tipo' => $d->tipo_documento,
                'nombre_archivo' => $d->nombre_archivo,
                'tamanho_bytes' => $d->tamanho_bytes,
                'fecha_subida' => $d->fecha_subida?->format('Y-m-d H:i:s'),
                'url' => Storage::url($d->ruta_archivo),
            ]),
        ];
    }

    /**
     * Subir un documento digitalizado
     */
    public function uploadDocumento(
        int $tramiteId,
        string $tipoDocumento,
        UploadedFile $archivo,
        int $operadorId
    ): array {
        DB::beginTransaction();

        try {
            $tramite = Tramite::findOrFail($tramiteId);

            // Verificar que el trámite esté en estado válido
            $estadosPermitidos = ['VALIDADO', 'EN_DIGITALIZACION'];
            if (!in_array($tramite->estadoActual->nombre, $estadosPermitidos)) {
                throw new \Exception('El trámite no está en un estado válido para digitalización');
            }

            // Cambiar estado a EN_DIGITALIZACION si es el primer documento
            if ($tramite->estadoActual->nombre === 'VALIDADO') {
                $estadoDigitalizacion = EstadoTramite::where('nombre', 'EN_DIGITALIZACION')->first();
                $tramite->update(['estado_actual' => $estadoDigitalizacion->id]);
            }

            // Generar nombre único del archivo
            $estudiante = $tramite->postulacion->estudiante;
            $ci = $estudiante->user->ci;
            $extension = $archivo->getClientOriginalExtension();
            $timestamp = now()->format('Ymd_His');
            $hash = Str::random(6);
            $nombreArchivo = "{$tipoDocumento}_{$ci}_{$timestamp}_{$hash}.{$extension}";

            // Almacenar archivo
            $ruta = "tramites/{$tramiteId}/documentos";
            $rutaCompleta = $archivo->storeAs($ruta, $nombreArchivo, 'public');

            // Guardar en base de datos
            $documento = Documento::create([
                'id_tramite' => $tramiteId,
                'tipo_documento' => $tipoDocumento,
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaCompleta,
                'tamanho_bytes' => $archivo->getSize(),
                'mime_type' => $archivo->getMimeType(),
                'validado' => true,
                'fecha_subida' => now(),
                'subido_por' => $operadorId,
            ]);

            DB::commit();

            return [
                'id' => $documento->id,
                'tipo' => $documento->tipo_documento,
                'nombre_archivo' => $documento->nombre_archivo,
                'tamanho_bytes' => $documento->tamanho_bytes,
                'url' => Storage::url($documento->ruta_archivo),
                'fecha_subida' => $documento->fecha_subida->format('Y-m-d H:i:s'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Eliminar un documento digitalizado
     */
    public function eliminarDocumento(int $documentoId, int $operadorId): void
    {
        DB::beginTransaction();

        try {
            $documento = Documento::findOrFail($documentoId);

            // Verificar que el operador tenga permisos
            if ($documento->subido_por !== $operadorId && !auth()->user()->hasRole(['Dpto. Sistema', 'Super Admin'])) {
                throw new \Exception('No tienes permisos para eliminar este documento');
            }

            // Eliminar archivo físico
            if (Storage::disk('public')->exists($documento->ruta_archivo)) {
                Storage::disk('public')->delete($documento->ruta_archivo);
            }

            // Eliminar registro
            $documento->delete();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Completar digitalización de un trámite
     */
    public function completarDigitalizacion(int $tramiteId, int $operadorId): array
    {
        DB::beginTransaction();

        try {
            $tramite = Tramite::with('documentos')->findOrFail($tramiteId);

            // Verificar que todos los documentos obligatorios estén digitalizados
            $documentosObligatorios = ['CI', 'KARDEX', 'COMPROBANTE_DOMICILIO'];
            $documentosDigitalizados = $tramite->documentos->pluck('tipo_documento')->toArray();

            foreach ($documentosObligatorios as $tipo) {
                if (!in_array($tipo, $documentosDigitalizados)) {
                    throw new \Exception("Falta digitalizar: {$tipo}");
                }
            }

            // Cambiar estado a DIGITALIZADO
            $estadoDigitalizado = EstadoTramite::where('nombre', 'DIGITALIZADO')->first();
            $tramite->update(['estado_actual' => $estadoDigitalizado->id]);

            // Registrar en historial
            $tramite->historial()->create([
                'estado_anterior' => 'EN_DIGITALIZACION',
                'estado_nuevo' => 'DIGITALIZADO',
                'observaciones' => 'Todos los documentos han sido digitalizados correctamente',
                'revisador_por' => $operadorId,
                'fecha_revision' => now(),
            ]);

            // Crear notificación
            Notificacion::create([
                'id_estudiante' => $tramite->postulacion->id_estudiante,
                'id_tramite' => $tramite->id,
                'tipo' => 'INFORMACION',
                'titulo' => 'Digitalización completa',
                'mensaje' => 'Tu expediente ha sido digitalizado. El próximo paso es la clasificación automática.',
                'leido' => false,
                'canal' => 'sistema',
            ]);

            // Disparar evento para clasificación automática
            event(new \App\Events\TramiteDigitalizado($tramite));

            DB::commit();

            return [
                'success' => true,
                'message' => 'Digitalización completada exitosamente',
                'tramite' => $tramite,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener expediente digitalizado completo
     */
    public function obtenerExpedienteCompleto(int $tramiteId): ?array
    {
        $tramite = Tramite::with([
            'postulacion.estudiante.user',
            'postulacion.beca',
            'estadoActual',
            'documentos',
        ])->find($tramiteId);

        if (!$tramite) {
            return null;
        }

        return [
            'tramite' => [
                'id' => $tramite->id,
                'codigo' => $tramite->codigo,
                'estado' => $tramite->estadoActual->nombre,
            ],
            'estudiante' => [
                'nombre' => $tramite->postulacion->estudiante->user->name,
                'ci' => $tramite->postulacion->estudiante->user->ci,
            ],
            'beca' => [
                'nombre' => $tramite->postulacion->beca->nombre,
            ],
            'documentos' => $tramite->documentos->map(fn($d) => [
                'id' => $d->id,
                'tipo' => $d->tipo_documento,
                'nombre_archivo' => $d->nombre_archivo,
                'tamanho_kb' => round($d->tamanho_bytes / 1024, 2),
                'mime_type' => $d->mime_type,
                'fecha_subida' => $d->fecha_subida->format('Y-m-d H:i:s'),
                'url_descarga' => route('operador.documentos.descargar', $d->id),
                'url_ver' => route('operador.documentos.ver', $d->id),
            ]),
        ];
    }

    /**
     * Descargar documento
     */
    public function descargarDocumento(int $documentoId)
    {
        $documento = Documento::findOrFail($documentoId);

        if (!Storage::disk('public')->exists($documento->ruta_archivo)) {
            throw new \Exception('Archivo no encontrado');
        }

        return Storage::disk('public')->download(
            $documento->ruta_archivo,
            $documento->nombre_archivo
        );
    }

    /**
     * Ver documento (preview)
     */
    public function verDocumento(int $documentoId)
    {
        $documento = Documento::findOrFail($documentoId);

        if (!Storage::disk('public')->exists($documento->ruta_archivo)) {
            throw new \Exception('Archivo no encontrado');
        }

        $rutaCompleta = Storage::disk('public')->path($documento->ruta_archivo);

        return response()->file($rutaCompleta, [
            'Content-Type' => $documento->mime_type,
            'Content-Disposition' => 'inline; filename="' . $documento->nombre_archivo . '"',
        ]);
    }
}
