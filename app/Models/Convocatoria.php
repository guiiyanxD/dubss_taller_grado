<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convocatoria extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'convocatoria';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio' => 'date',
            'fecha_fin' => 'date'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación: becas
     */
    public function becas()
    {
        return $this->hasMany(Beca::class, 'id_convocatoria');
    }

    /**
     * Relación: postulaciones
     */
    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'id_convocatoria');
    }
}
