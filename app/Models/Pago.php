<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Pago (cobro) de una orden de reparación.
 *
 * Estados: pendiente → aprobado | rechazado | anulado ; aprobado → reintegrado.
 * Las transiciones se controlan exclusivamente desde App\Services\Pagos\PagoService.
 */
class Pago extends Model
{
    use Auditable;

    public const MEDIOS = [
        'efectivo'        => 'Efectivo',
        'transferencia'   => 'Transferencia bancaria',
        'mercado_pago'    => 'Mercado Pago',
        'modo'            => 'MODO',
        'tarjeta_debito'  => 'Tarjeta de débito',
        'tarjeta_credito' => 'Tarjeta de crédito',
    ];

    public const ESTADOS = [
        'pendiente'   => 'Pendiente',
        'aprobado'    => 'Aprobado',
        'rechazado'   => 'Rechazado',
        'anulado'     => 'Anulado',
        'reintegrado' => 'Reintegrado',
    ];

    protected $fillable = [
        'codigo', 'reparacion_id', 'user_id', 'medio_pago', 'estado', 'monto', 'cuotas',
        'recargo', 'monto_total', 'referencia_externa', 'url_pago', 'comprobante',
        'datos_pasarela', 'observaciones', 'motivo_rechazo', 'fecha_aprobacion',
    ];

    protected $casts = [
        'monto'            => 'decimal:2',
        'recargo'          => 'decimal:2',
        'monto_total'      => 'decimal:2',
        'cuotas'           => 'integer',
        'datos_pasarela'   => 'array',
        'fecha_aprobacion' => 'datetime',
    ];

    /** La respuesta cruda de la pasarela no aporta a la trazabilidad funcional. */
    protected array $camposExcluidosAuditoria = ['datos_pasarela'];

    protected static function booted(): void
    {
        static::creating(function (Pago $pago) {
            $pago->codigo ??= (string) Str::uuid();
        });
    }

    public function reparacion(): BelongsTo
    {
        return $this->belongsTo(Reparacion::class)->withTrashed();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getMedioTextoAttribute(): string
    {
        return self::MEDIOS[$this->medio_pago] ?? $this->medio_pago;
    }

    public function getEstadoTextoAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'pendiente'   => 'warning',
            'aprobado'    => 'success',
            'rechazado'   => 'danger',
            'anulado'     => 'secondary',
            'reintegrado' => 'info',
            default       => 'secondary',
        };
    }

    public function getMedioIconoAttribute(): string
    {
        return match ($this->medio_pago) {
            'efectivo'        => 'bi-cash-coin',
            'transferencia'   => 'bi-bank',
            'mercado_pago'    => 'bi-wallet2',
            'modo'            => 'bi-phone',
            'tarjeta_debito'  => 'bi-credit-card',
            'tarjeta_credito' => 'bi-credit-card-2-front',
            default           => 'bi-cash',
        };
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'aprobado');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
}
