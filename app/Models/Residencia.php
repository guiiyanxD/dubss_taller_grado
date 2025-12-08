<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residencia extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'residencia';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_formulario',
        'provincia',
        'zona',
        'calle',
        'cant_banhos',
        'cant_salas',
        'cant_dormitorios',
        'cantt_comedor',
        'barrio',
        'cant_patios',
        'puntaje_total'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cant_banhos' => 'integer',
            'cant_salas' => 'integer',
            'cant_dormitorios' => 'integer',
            'cantt_comedor' => 'integer',
            'cant_patios' => 'integer',
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
}
