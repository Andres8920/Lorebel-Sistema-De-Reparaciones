<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Auditoría de inicios y cierres de sesión (login / logout / login fallido).
 */
class RegistroSesion extends Model
{
    protected $table = 'registro_sesiones';

    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'email', 'evento', 'ip', 'user_agent'];

    protected $casts = ['created_at' => 'datetime'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
