<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de trazabilidad. Es de solo inserción: no se modifica ni se elimina.
 */
class Auditoria extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'usuario_nombre', 'accion', 'entidad', 'entidad_id',
        'valores_anteriores', 'valores_nuevos', 'ip', 'user_agent',
    ];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos'     => 'array',
        'created_at'         => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getAccionColorAttribute(): string
    {
        return match ($this->accion) {
            'alta'         => 'success',
            'modificacion' => 'warning',
            'baja'         => 'danger',
            default        => 'secondary',
        };
    }

    /** Campos involucrados en el cambio, para mostrar "antes → después". */
    public function getCamposAttribute(): array
    {
        return array_values(array_unique(array_merge(
            array_keys($this->valores_anteriores ?? []),
            array_keys($this->valores_nuevos ?? [])
        )));
    }
}
