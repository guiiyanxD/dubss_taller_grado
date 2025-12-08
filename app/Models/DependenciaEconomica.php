<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DependenciaEconomica extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'dependencia_economica';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_formulario',
        'tipo_dependencia',
        'nota_ocupacion_dependiente',
        'id_ocupacion_dependiente',
        'puntaje',
        'puntaje_total'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'puntaje' => 'decimal:1',
            'puntaje_total' => 'decimal:1'
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
     * Relación: ingresoEconomico
     */
    public function ingresosEconomicos()
    {
        return $this->hasOne(IngresoEconomico::class, 'id_dependencia_eco');
    }

    /**
     * Relación: tiposOcupacion
     */
    public function tiposOcupacion()
    {
        return $this->hasMany(TipoOcupacionDependiente::class, 'id_dependencia_eco');
    }
}
