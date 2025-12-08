<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beca extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'beca';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'version',
        'periodo',
        'id_convocatoria',
        'cupos_disponibles'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cupos_disponibles' => 'integer'
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación: convocatoria
     */
    public function convocatoria()
    {
        return $this->belongsTo(Convocatoria::class, 'id_convocatoria');
    }

    /**
     * Relación: requisitos
     */
    public function requisitos()
    {
        return $this->belongsToMany(Requisito::class, 'beca_requisito', 'id_beca', 'id_requisito');
    }

    /**
     * Relación: postulaciones
     */
    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'id_beca');
    }
}
