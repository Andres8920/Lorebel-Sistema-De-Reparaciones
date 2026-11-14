<?php

namespace App\Services\Pagos\Pasarelas;

use App\Models\Pago;
use App\Services\Pagos\ResultadoPasarela;

/**
 * Tarjeta de débito / crédito cobrada con la terminal (posnet) del taller.
 * El cajero registra el número de cupón; la operación queda aprobada.
 */
class TarjetaPasarela implements PasarelaPago
{
    public function iniciar(Pago $pago): ResultadoPasarela
    {
        if (blank($pago->referencia_externa)) {
            return ResultadoPasarela::rechazado('Debe informar el número de cupón de la terminal.');
        }

        return ResultadoPasarela::aprobado($pago->referencia_externa, [
            'cuotas'  => $pago->cuotas,
            'recargo' => (float) $pago->recargo,
        ]);
    }

    public function consultar(Pago $pago, ?string $referenciaExterna = null): ResultadoPasarela
    {
        return ResultadoPasarela::aprobado($pago->referencia_externa);
    }

    public function enSimulacion(): bool
    {
        return false;
    }
}
