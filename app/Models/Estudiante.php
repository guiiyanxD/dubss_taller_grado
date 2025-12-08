<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'estudiante';

    /**
     * La clave primaria de la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id_usuario';

    /**
     * Indica si la clave primaria es auto-incremental.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_usuario',
        'nro_registro',
        'carrera',
        'semestre',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'semestre' => 'integer',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Obtener el usuario asociado (relación inversa)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * Obtener los formularios socioeconómicos del estudiante
     */
    public function formulariosSocioEconomicos()
    {
        return $this->hasMany(FormularioSocioEconomico::class, 'id_estudiante');
    }

    /**
     * Obtener el formulario socioeconómico más reciente
     */
    public function formularioActual()
    {
        return $this->hasOne(FormularioSocioEconomico::class, 'id_estudiante')
            ->latestOfMany();
    }

    /**
     * Obtener las postulaciones del estudiante
     */
    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'id_estudiante');
    }

    /**
     * Obtener las notificaciones del estudiante
     */
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_estudiante');
    }

    // =====================================================
    // ACCESSORS & MUTATORS
    // =====================================================

    /**
     * Obtener el nombre completo del estudiante (desde usuario)
     */
    public function getNombreCompletoAttribute(): string
    {
        return $this->usuario->nombre_completo ?? 'N/A';
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope para filtrar por carrera
     */
    public function scopePorCarrera($query, $carrera)
    {
        return $query->where('carrera', $carrera);
    }

    /**
     * Scope para filtrar por semestre
     */
    public function scopePorSemestre($query, $semestre)
    {
        return $query->where('semestre', $semestre);
    }

    /**
     * Scope para estudiantes con formulario completado
     */
    public function scopeConFormularioCompleto($query)
    {
        return $query->whereHas('formulariosSocioEconomicos', function ($q) {
            $q->where('completado', true);
        });
    }

    // =====================================================
    // MÉTODOS AUXILIARES
    // =====================================================

    /**
     * Verificar si el estudiante tiene un formulario completado
     */
    public function tieneFormularioCompleto(): bool
    {
        return $this->formulariosSocioEconomicos()
            ->where('completado', true)
            ->exists();
    }

    /**
     * Obtener las postulaciones activas (no rechazadas)
     */
    public function postulacionesActivas()
    {
        return $this->postulaciones()
            ->whereNotIn('estado_postulado', ['RECHAZADO', 'DENEGADO']);
    }
}
