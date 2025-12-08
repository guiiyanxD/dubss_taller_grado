<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BecaRequisito extends Pivot
{
    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'beca_requisito';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_beca',
        'id_requisito',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Obtener la beca asociada
     */
    public function beca()
    {
        return $this->belongsTo(Beca::class, 'id_beca');
    }

    /**
     * Obtener el requisito asociado
     */
    public function requisito()
    {
        return $this->belongsTo(Requisito::class, 'id_requisito');
    }
}
