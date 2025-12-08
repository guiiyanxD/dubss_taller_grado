<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoTramite extends Model
{
    /**
     * IMPORTANTE: Esta tabla NO tiene timestamps (created_at ni updated_at)
     * Es una tabla de catálogo estática
     */
    public $timestamps = false; // ← DESHABILITAR TIMESTAMPS COMPLETAMENTE

    protected $table = 'estado_tramite';

    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    /**
     * Relación: Un estado puede tener muchos trámites
     */
    public function tramites(): HasMany
    {
        return $this->hasMany(Tramite::class, 'estado_actual');
    }

    /**
     * Scope: Obtener estados ordenados
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden');
    }
}
