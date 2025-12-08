<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalAdministrativo extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'personal_administrativo';

    /**
     * La clave primaria de la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id_usuario';

    /**
     * Indica si la clave primaria es auto-incremental.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_usuario',
        'cargo',
        'departamento',
        'activo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Obtener el usuario asociado (relación inversa)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope para filtrar solo personal activo
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por departamento
     */
    public function scopePorDepartamento($query, $departamento)
    {
        return $query->where('departamento', $departamento);
    }

    /**
     * Scope para filtrar por cargo
     */
    public function scopePorCargo($query, $cargo)
    {
        return $query->where('cargo', $cargo);
    }

    // =====================================================
    // MÉTODOS AUXILIARES
    // =====================================================

    /**
     * Verificar si el personal está activo
     */
    public function estaActivo(): bool
    {
        return $this->activo === true;
    }

    /**
     * Desactivar personal
     */
    public function desactivar(): void
    {
        $this->update(['activo' => false]);
    }

    /**
     * Activar personal
     */
    public function activar(): void
    {
        $this->update(['activo' => true]);
    }
}
