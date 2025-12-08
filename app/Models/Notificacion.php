<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'notificacion';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_estudiante',
        'id_tramite',
        'tipo',
        'titulo',
        'mensaje',
        'leido',
        'fecha_creacion',
        'fecha_lectura',
        'canal'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'leido' => 'boolean',
            'fecha_creacion' => 'date',
            'fecha_lectura' => 'date'
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
     * Relación: tramite
     */
    public function tramite()
    {
        return $this->belongsTo(Tramite::class, 'id_tramite');
    }
}
