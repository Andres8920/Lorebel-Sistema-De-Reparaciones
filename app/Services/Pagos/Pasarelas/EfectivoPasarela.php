<?php

namespace App\Services\Pagos\Pasarelas;

use App\Models\Pago;
use App\Services\Pagos\ResultadoPasarela;

/**
 * Cobro en efectivo en el mostrador: se acredita en el momento.
 */
class EfectivoPasarela implements PasarelaPago
{
    public function iniciar(Pago $pago): ResultadoPasarela
    {
        return ResultadoPasarela::aprobado('EF-' . str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT));
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
