<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tecnico extends Model
{
    use SoftDeletes;

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'especialidad',
        'activo',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Obtener el nombre completo del técnico
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    /**
     * Relación: Un técnico tiene muchas reparaciones
     */
    public function reparaciones(): HasMany
    {
        return $this->hasMany(Reparacion::class);
    }

    /**
     * Scope: Solo técnicos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
