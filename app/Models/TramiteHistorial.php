<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TramiteHistorial extends Model
{
    /**
     * IMPORTANTE: Esta tabla solo tiene created_at, NO tiene updated_at
     * Por eso configuramos timestamps personalizados
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null; // ← ESTA ES LA CLAVE

    protected $table = 'tramite_historial';

    protected $fillable = [
        'id_tramite',
        'estado_anterior',
        'estado_nuevo',
        'observaciones',
        'revisador_por',
        'fecha_revision',
    ];

    protected $casts = [
        'fecha_revision' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Relación: El historial pertenece a un trámite
     */
    public function tramite(): BelongsTo
    {
        return $this->belongsTo(Tramite::class, 'id_tramite');
    }

    /**
     * Relación: El historial fue creado por un usuario (revisor)
     */
    public function revisador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisador_por');
    }
}
