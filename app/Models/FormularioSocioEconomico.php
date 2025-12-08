<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormularioSocioEconomico extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'formulario_socio_economico';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_estudiante',
        'validado_por',
        'fecha_llenado',
        'completado',
        'telefono_referencia',
        'comentario_personal',
        'observaciones',
        'discapacidad',
        'comentario_discapacidad',
        'otro_beneficio',
        'comentario_otro_beneficio',
        'lugar_procedencia'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'validado_por' => 'boolean',
            'fecha_llenado' => 'date',
            'completado' => 'boolean',
            'discapacidad' => 'boolean',
            'otro_beneficio' => 'boolean'
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
     * Relación: grupoFamiliar
     */
    public function grupoFamiliar()
    {
        return $this->hasOne(GrupoFamiliar::class, 'id_formulario');
    }

    /**
     * Relación: dependenciaEconomica
     */
    public function dependenciaEconomica()
    {
        return $this->hasOne(DependenciaEconomica::class, 'id_formulario');
    }

    /**
     * Relación: residencia
     */
    public function residencia()
    {
        return $this->hasOne(Residencia::class, 'id_formulario');
    }

    /**
     * Relación: tenenciaVivienda
     */
    public function tenenciaVivienda()
    {
        return $this->hasOne(TenenciaVivienda::class, 'id_formulario');
    }

    /**
     * Relación: postulaciones
     */
    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'id_formulario');
    }
}
