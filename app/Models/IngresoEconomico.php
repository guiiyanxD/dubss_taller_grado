<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngresoEconomico extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'ingreso_economico';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_dependencia_eco',
        'rango_monto',
        'puntaje'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'puntaje' => 'decimal:2'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación: dependenciaEconomica
     */
    public function dependenciaEconomica()
    {
        return $this->belongsTo(DependenciaEconomica::class, 'id_dependencia_eco');
    }
}
