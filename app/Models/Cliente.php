<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\Auditable;

class Cliente extends Model
{
    use SoftDeletes, Auditable;

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nombre',
        'telefono',
        'whatsapp',
        'email',
        'dni_cuit',
        'notas_internas',
    ];

    /**
     * Los atributos que deben ser tratados como fechas
     */
    protected $dates = ['deleted_at'];

    /**
     * Relación: Un cliente puede tener muchas reparaciones
     */
    public function reparaciones(): HasMany
    {
        return $this->hasMany(Reparacion::class);
    }

    /**
     * Obtener el historial de reparaciones ordenado por fecha
     */
    public function historialReparaciones()
    {
        return $this->reparaciones()->orderBy('fecha_ingreso', 'desc');
    }
}
