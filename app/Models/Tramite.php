<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tramite extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'tramite';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_postulacion',
        'codigo',
        'fecha_creacion',
        'clasificado',
        'fecha_clasificacion',
        'estado_actual'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_creacion' => 'date',
            'fecha_clasificacion' => 'date'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación: postulacion
     */
    public function postulacion()
    {
        return $this->belongsTo(Postulacion::class, 'id_postulacion');
    }

    /**
     * Relación: estadoActual
     */
    public function estadoActual()
    {
        return $this->belongsTo(EstadoTramite::class, 'estado_actual');
    }

    /**
     * Relación: historial
     */
    public function historial()
    {
        return $this->hasMany(TramiteHistorial::class, 'id_tramite');
    }

    /**
     * Relación: documentos
     */
    public function documentos()
    {
        return $this->hasMany(Documento::class, 'id_tramite');
    }

    /**
     * Relación: notificaciones
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_tramite');
    }
}
