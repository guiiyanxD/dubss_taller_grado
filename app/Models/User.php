<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nombres',
        'apellidos',
        'ci',
        'telefono',
        'ciudad',
        'fecha_nacimiento',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
        ];
    }

    // =====================================================
    // RELACIONES
    // =====================================================

    /**
     * Relación polimórfica: Usuario puede ser Estudiante o Personal Administrativo
     * (Pero usamos herencia de tabla simple, así que no es polimórfica realmente)
     */
    
    /**
     * Obtener el perfil de estudiante si existe
     */
    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id_usuario');
    }

    /**
     * Obtener el perfil de personal administrativo si existe
     */
    public function personalAdministrativo()
    {
        return $this->hasOne(PersonalAdministrativo::class, 'id_usuario');
    }

    /**
     * Postulaciones creadas por este usuario (como admin)
     */
    public function postulacionesCreadas()
    {
        return $this->hasMany(Postulacion::class, 'creado_por');
    }

    /**
     * Documentos digitalizados por este usuario
     */
    public function documentosDigitalizados()
    {
        return $this->hasMany(Documento::class, 'digitalizado_por');
    }

    /**
     * Documentos validados por este usuario
     */
    public function documentosValidados()
    {
        return $this->hasMany(Documento::class, 'validado_por');
    }

    /**
     * Historial de trámites revisados
     */
    public function tramitesRevisados()
    {
        return $this->hasMany(TramiteHistorial::class, 'revisador_por');
    }

    // =====================================================
    // ACCESSORS & MUTATORS
    // =====================================================

    /**
     * Obtener el nombre completo del usuario
     */
    public function getNombreCompletoAttribute(): string
    {
        if ($this->nombres && $this->apellidos) {
            return "{$this->nombres} {$this->apellidos}";
        }
        return $this->name;
    }

    /**
     * Verificar si el usuario es estudiante
     */
    public function esEstudiante(): bool
    {
        return $this->estudiante()->exists();
    }

    /**
     * Verificar si el usuario es personal administrativo
     */
    public function esPersonalAdministrativo(): bool
    {
        return $this->personalAdministrativo()->exists();
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope para filtrar solo estudiantes
     */
    public function scopeEstudiantes($query)
    {
        return $query->whereHas('estudiante');
    }

    /**
     * Scope para filtrar solo personal administrativo
     */
    public function scopePersonalAdministrativo($query)
    {
        return $query->whereHas('personalAdministrativo');
    }

    /**
     * Scope para buscar por CI
     */
    public function scopePorCi($query, $ci)
    {
        return $query->where('ci', $ci);
    }
}
