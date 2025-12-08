<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenenciaVivienda extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'tenencia_vivienda';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_formulario',
        'tipo_tenencia',
        'detalle_tenencia',
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
     * Relación: tiposTenencia
     */
    public function tiposTenencia()
    {
        return $this->hasMany(TipoTenenciaVivienda::class, 'id_tenencia_vivienda');
    }
}
