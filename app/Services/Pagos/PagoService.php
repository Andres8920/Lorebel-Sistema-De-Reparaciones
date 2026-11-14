<?php

namespace App\Services\Pagos;

use App\Models\Pago;
use App\Models\Reparacion;
use App\Models\User;
use App\Services\Pagos\Pasarelas\PasarelaFactory;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de dominio del requerimiento CORE de la Iteración 2:
 * "Registrar cobro de orden de reparación".
 *
 * Concentra las reglas de negocio y las transiciones de estado del pago.
 *
 *  RN01  No se puede cobrar una orden cancelada.
 *  RN02  El presupuesto debe estar aprobado por el cliente.
 *  RN03  El monto debe ser mayor a cero.
 *  RN04  El monto no puede superar el saldo disponible (saldo − pagos pendientes de acreditación).
 *  RN05  Antes de que el equipo esté "listo", solo se cobra una seña de hasta el 50 % del importe.
 *  RN06  Solo la tarjeta de crédito admite cuotas (1, 3 o 6) y aplica recargo financiero.
 *  RN07  Transferencia, Mercado Pago y MODO quedan PENDIENTES hasta su acreditación.
 *  RN08  Solo se anula / rechaza un pago pendiente; solo se reintegra un pago aprobado.
 *  RN09  La orden no puede pasar a "entregado" si tiene saldo pendiente (OrdenController).
 *  RN10  Las notificaciones repetidas de la pasarela no duplican el cobro (idempotencia).
 */
class PagoService
{
    public function __construct(private PasarelaFactory $pasarelas)
    {
    }

    /**
     * Valida si es posible registrar un cobro sobre la orden.
     * (Método utilizado para la prueba de caja blanca - ruta básica.)
     *
     * @throws ReglaNegocioException
     */
    public function validarPago(Reparacion $orden, string $medio, float $monto, int $cuotas = 1): void
    {
        if ($orden->estado === 'cancelado') {                                           // RN01
            throw new ReglaNegocioException('No se puede cobrar una orden cancelada.');
        }

        if ($orden->presupuesto_aprobado !== true) {                                     // RN02
            throw new ReglaNegocioException('El presupuesto debe estar aprobado por el cliente antes de cobrar.');
        }

        if ($monto <= 0) {                                                               // RN03
            throw new ReglaNegocioException('El monto a cobrar debe ser mayor a cero.');
        }

        $maximo = $this->montoMaximoCobrable($orden);
        if (round($monto, 2) > round($maximo, 2)) {                                      // RN04 / RN05
            throw new ReglaNegocioException(sprintf(
                'El monto supera el máximo cobrable en este momento ($ %s).',
                number_format($maximo, 2, ',', '.')
            ));
        }

        if ($medio === 'tarjeta_credito') {                                              // RN06
            if (! array_key_exists($cuotas, config('pagos.recargos_cuotas'))) {
                throw new ReglaNegocioException('La cantidad de cuotas seleccionada no está habilitada.');
            }
        } elseif ($cuotas !== 1) {
            throw new ReglaNegocioException('Solo la tarjeta de crédito admite pago en cuotas.');
        }
    }

    /**
     * Monto máximo que puede cobrarse ahora sobre la orden (RN04 y RN05).
     */
    public function montoMaximoCobrable(Reparacion $orden): float
    {
        $pendientes = $orden->total_pendiente_acreditacion;
        $disponible = max(0, $orden->saldo - $pendientes);

        if (in_array($orden->estado, ['listo', 'entregado'], true)) {
            return round($disponible, 2);
        }

        // Equipo aún en taller: solo seña hasta el % configurado del importe total.
        $tope = $orden->importe_a_cobrar * config('pagos.sena_maxima_porcentaje') / 100;
        $comprometido = $orden->total_pagado + $pendientes;

        return round(max(0, min($disponible, $tope - $comprometido)), 2);
    }

    /**
     * Recargo financiero según cuotas (RN06).
     */
    public function calcularRecargo(string $medio, float $monto, int $cuotas = 1): float
    {
        if ($medio !== 'tarjeta_credito') {
            return 0.0;
        }
        $porcentaje = config('pagos.recargos_cuotas')[$cuotas] ?? 0;

        return round($monto * $porcentaje / 100, 2);
    }

    /**
     * Registra un cobro e inicia la operación en la pasarela correspondiente.
     *
     * @return array{0: Pago, 1: ResultadoPasarela}
     * @throws ReglaNegocioException
     */
    public function iniciarPago(
        Reparacion $orden,
        string $medio,
        float $monto,
        int $cuotas = 1,
        ?string $referencia = null,
        ?string $observaciones = null,
        ?User $usuario = null,
    ): array {
        return DB::transaction(function () use ($orden, $medio, $monto, $cuotas, $referencia, $observaciones, $usuario) {
            // Bloqueo de la orden para evitar cobros concurrentes que superen el saldo.
            $orden = Reparacion::whereKey($orden->getKey())->lockForUpdate()->firstOrFail();

            $this->validarPago($orden, $medio, $monto, $cuotas);

            $recargo = $this->calcularRecargo($medio, $monto, $cuotas);

            $pago = Pago::create([
                'reparacion_id'      => $orden->id,
                'user_id'            => $usuario?->id,
                'medio_pago'         => $medio,
                'estado'             => 'pendiente',
                'monto'              => round($monto, 2),
                'cuotas'             => $cuotas,
                'recargo'            => $recargo,
                'monto_total'        => round($monto + $recargo, 2),
                'referencia_externa' => $referencia,
                'observaciones'      => $observaciones,
            ]);

            $resultado = $this->pasarelas->para($medio)->iniciar($pago);

            $this->aplicarResultado($pago, $resultado);

            return [$pago->fresh(), $resultado];
        });
    }

    /**
     * Aplica al pago el resultado informado por la pasarela (al iniciar,
     * por webhook, por URL de retorno o por el simulador). Idempotente (RN10).
     */
    public function aplicarResultado(Pago $pago, ResultadoPasarela $resultado): Pago
    {
        if (! $pago->estaPendiente()) {
            return $pago; // ya fue resuelto: una notificación repetida no tiene efecto
        }

        return match ($resultado->estado) {
            ResultadoPasarela::APROBADO  => $this->aprobar($pago, $resultado->referencia, $resultado->datos),
            ResultadoPasarela::RECHAZADO => $this->rechazar($pago, $resultado->mensaje ?? 'Rechazado por la pasarela', $resultado->referencia, $resultado->datos),
            default                      => $this->registrarPendiente($pago, $resultado),
        };
    }

    /**
     * Confirmación manual de una transferencia bancaria (RN07).
     */
    public function confirmarTransferencia(Pago $pago, string $numeroOperacion, ?string $comprobante = null): Pago
    {
        if ($pago->medio_pago !== 'transferencia') {
            throw new ReglaNegocioException('Solo las transferencias se confirman manualmente.');
        }
        if (! $pago->estaPendiente()) {
            throw new ReglaNegocioException('La transferencia ya fue procesada.');
        }
        if ($comprobante) {
            $pago->comprobante = $comprobante;
        }

        return $this->aprobar($pago, $numeroOperacion);
    }

    public function anular(Pago $pago, string $motivo): Pago
    {
        if (! $pago->estaPendiente()) {                                                  // RN08
            throw new ReglaNegocioException('Solo se pueden anular pagos pendientes.');
        }
        $pago->update(['estado' => 'anulado', 'motivo_rechazo' => $motivo]);

        return $pago;
    }

    public function reintegrar(Pago $pago, string $motivo): Pago
    {
        if ($pago->estado !== 'aprobado') {                                              // RN08
            throw new ReglaNegocioException('Solo se pueden reintegrar pagos aprobados.');
        }
        $pago->update(['estado' => 'reintegrado', 'motivo_rechazo' => $motivo]);

        return $pago;
    }

    private function aprobar(Pago $pago, ?string $referencia = null, array $datos = []): Pago
    {
        $pago->fill([
            'estado'             => 'aprobado',
            'fecha_aprobacion'   => now(),
            'referencia_externa' => $referencia ?? $pago->referencia_externa,
        ]);
        if ($datos) {
            $pago->datos_pasarela = array_merge($pago->datos_pasarela ?? [], $datos);
        }
        $pago->save();

        return $pago;
    }

    private function rechazar(Pago $pago, string $motivo, ?string $referencia = null, array $datos = []): Pago
    {
        $pago->fill([
            'estado'             => 'rechazado',
            'motivo_rechazo'     => mb_substr($motivo, 0, 255),
            'referencia_externa' => $referencia ?? $pago->referencia_externa,
        ]);
        if ($datos) {
            $pago->datos_pasarela = array_merge($pago->datos_pasarela ?? [], $datos);
        }
        $pago->save();

        return $pago;
    }

    private function registrarPendiente(Pago $pago, ResultadoPasarela $resultado): Pago
    {
        if ($resultado->urlRedireccion !== null) {
            $pago->url_pago = $resultado->urlRedireccion;
        }
        if ($resultado->referencia !== null) {
            $pago->referencia_externa = $resultado->referencia;
        }
        if ($resultado->datos) {
            $pago->datos_pasarela = array_merge($pago->datos_pasarela ?? [], $resultado->datos);
        }
        if ($pago->isDirty()) {
            $pago->save();
        }

        return $pago;
    }
}
