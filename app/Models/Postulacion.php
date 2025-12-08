<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'postulacion';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_estudiante',
        'id_convocatoria',
        'id_formulario',
        'id_beca',
        'fecha_postulacion',
        'estado_postulado',
        'motivo_rechazo',
        'posicion_ranking',
        'puntaje_final',
        'creado_por'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_postulacion' => 'date',
        'posicion_ranking' => 'integer',
        'puntaje_final' => 'decimal:2'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación: estudiante
     */
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_usuario');
    }

    /**
     * Relación: convocatoria
     */
    public function convocatoria()
    {
        return $this->belongsTo(Convocatoria::class, 'id_convocatoria');
    }

    /**
     * Relación: formulario
     */
    public function formulario()
    {
        return $this->belongsTo(FormularioSocioEconomico::class, 'id_formulario');
    }

    /**
     * Relación: beca
     */
    public function beca()
    {
        return $this->belongsTo(Beca::class, 'id_beca');
    }

    /**
     * Relación: creador
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Relación: tramite
     */
    public function tramite()
    {
        return $this->hasOne(Tramite::class, 'id_postulacion');
    }
}
