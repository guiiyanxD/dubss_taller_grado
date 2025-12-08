<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoTenenciaVivienda extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'tipo_tenencia_vivienda';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_tenencia_vivienda',
        'nombre',
        'documento_adjuntar',
        'puntaje'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'puntaje' => 'decimal:1'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación: tenenciaVivienda
     */
    public function tenenciaVivienda()
    {
        return $this->belongsTo(TenenciaVivienda::class, 'id_tenencia_vivienda');
    }
}
