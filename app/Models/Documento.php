<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'documento';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_tramite',
        'tipo_documento',
        'nombre_archivo',
        'ruta_digital',
        'estado_fisico',
        'digitalizado_por',
        'fecha_presentacion',
        'fecha_digitalizacion',
        'observaciones',
        'motivo_rechazo',
        'validado_por'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_presentacion' => 'date',
            'fecha_digitalizacion' => 'date'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación: tramite
     */
    public function tramite()
    {
        return $this->belongsTo(Tramite::class, 'id_tramite');
    }

    /**
     * Relación: digitalizadoPor
     */
    public function digitalizadoPor()
    {
        return $this->belongsTo(User::class, 'digitalizado_por');
    }

    /**
     * Relación: validadoPor
     */
    public function validadoPor()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
