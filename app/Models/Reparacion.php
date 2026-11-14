<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\Auditable;

class Reparacion extends Model
{
    use SoftDeletes, Auditable;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'reparaciones';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'cliente_id',
        'dispositivo',
        'marca',
        'modelo',
        'imei_serial',
        'problema_reportado',
        'diagnostico_tecnico',
        'trabajo_realizado',
        'estado',
        'fotos',
        'presupuesto',
        'presupuesto_aprobado',
        'fecha_aprobacion_presupuesto',
        'tecnico_id',
        'fecha_ingreso',
        'fecha_estimada_entrega',
        'fecha_entrega',
        'costo_repuestos',
        'costo_mano_obra',
        'costo_total',
    ];

    /**
     * Los atributos que deben ser casteados
     */
    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_estimada_entrega' => 'date',
        'fecha_entrega' => 'date',
        'fecha_aprobacion_presupuesto' => 'date',
        'presupuesto' => 'decimal:2',
        'costo_repuestos' => 'decimal:2',
        'costo_mano_obra' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'presupuesto_aprobado' => 'boolean',
        'fotos' => 'array', // Para almacenar rutas de múltiples imágenes
    ];

    /**
     * Relación: Una reparación pertenece a un cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Relación: Una reparación tiene muchos pagos (seña, saldo, pagos parciales)
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class)->latest();
    }

    /**
     * Importe a cobrar de la orden: el costo final si ya fue calculado,
     * caso contrario el presupuesto informado al cliente.
     */
    public function getImporteACobrarAttribute(): float
    {
        $total = (float) ($this->costo_total ?? 0);
        return $total > 0 ? $total : (float) ($this->presupuesto ?? 0);
    }

    /**
     * Total efectivamente cobrado (solo pagos aprobados, sin recargos financieros).
     */
    public function getTotalPagadoAttribute(): float
    {
        return (float) $this->pagos()->where('estado', 'aprobado')->sum('monto');
    }

    /**
     * Total en pagos pendientes de acreditación (transferencias, MP, MODO).
     */
    public function getTotalPendienteAcreditacionAttribute(): float
    {
        return (float) $this->pagos()->where('estado', 'pendiente')->sum('monto');
    }

    /**
     * Saldo pendiente de cobro.
     */
    public function getSaldoAttribute(): float
    {
        return round(max(0, $this->importe_a_cobrar - $this->total_pagado), 2);
    }

    /**
     * Estado de cobro de la orden: sin_cargo | impaga | parcial | pagada
     */
    public function getEstadoCobroAttribute(): string
    {
        if ($this->importe_a_cobrar <= 0) {
            return 'sin_importe';
        }
        if ($this->saldo <= 0) {
            return 'pagada';
        }
        return $this->total_pagado > 0 ? 'parcial' : 'impaga';
    }

    /**
     * Relación: Una reparación pertenece a un técnico
     */
    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(Tecnico::class);
    }

    /**
     * Obtener el texto del estado formateado
     */
    public function getEstadoTextoAttribute(): string
    {
        $estados = [
            'ingresado' => 'Ingresado',
            'en_reparacion' => 'En Reparación',
            'esperando_repuesto' => 'Esperando Repuesto',
            'listo' => 'Listo',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ];

        return $estados[$this->estado] ?? $this->estado;
    }

    /**
     * Obtener el color del badge según el estado
     */
    public function getEstadoColorAttribute(): string
    {
        $colores = [
            'ingresado' => 'secondary',
            'en_reparacion' => 'warning',
            'esperando_repuesto' => 'info',
            'listo' => 'success',
            'entregado' => 'primary',
            'cancelado' => 'danger',
        ];

        return $colores[$this->estado] ?? 'secondary';
    }

    /**
     * Obtener texto del estado de aprobación del presupuesto
     */
    public function getPresupuestoEstadoTextoAttribute(): string
    {
        if (is_null($this->presupuesto_aprobado)) {
            return 'Pendiente';
        }
        return $this->presupuesto_aprobado ? 'Aprobado' : 'Rechazado';
    }

    /**
     * Scope: Órdenes activas (no entregadas ni canceladas)
     */
    public function scopeActivas($query)
    {
        return $query->whereNotIn('estado', ['entregado', 'cancelado']);
    }

    /**
     * Scope: Órdenes por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
