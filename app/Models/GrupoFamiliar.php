<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoFamiliar extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'grupo_familiar';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_formulario',
        'cantidad_hijos',
        'cantidad_familiares',
        'tiene_hijos',
        'puntaje',
        'puntaje_total'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad_hijos' => 'integer',
        'cantidad_familiares' => 'integer',
        'tiene_hijos' => 'boolean',
        'puntaje' => 'decimal:2',
        'puntaje_total' => 'decimal:2'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación: formulario
     */
    public function formulario()
    {
        return $this->belongsTo(FormularioSocioEconomico::class, 'id_formulario');
    }

    /**
     * Relación: miembros
     */
    public function miembrosFamiliares()
    {
        return $this->hasMany(MiembroFamiliar::class, 'id_grupo_familiar');
    }
}
