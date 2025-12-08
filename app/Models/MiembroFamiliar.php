<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiembroFamiliar extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'miembro_familiar';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_grupo_familiar',
        'nombre_completo',
        'parentesco',
        'edad',
        'ocupacion',
        'observacion'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'edad' => 'integer'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación: grupoFamiliar
     */
    public function grupoFamiliar()
    {
        return $this->belongsTo(GrupoFamiliar::class, 'id_grupo_familiar');
    }
}
