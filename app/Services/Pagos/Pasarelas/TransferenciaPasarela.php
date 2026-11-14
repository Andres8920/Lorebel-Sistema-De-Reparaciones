<?php

namespace App\Services\Pagos\Pasarelas;

use App\Models\Pago;
use App\Services\Pagos\ResultadoPasarela;

/**
 * Transferencia bancaria (CBU / alias). El pago queda PENDIENTE hasta que
 * un usuario del taller verifica la acreditación en el home banking y la confirma
 * informando el número de operación y, opcionalmente, el comprobante.
 */
class TransferenciaPasarela implements PasarelaPago
{
    public function iniciar(Pago $pago): ResultadoPasarela
    {
        return ResultadoPasarela::pendiente(
            null,
            null,
            config('pagos.transferencia'),
            'Informe al cliente los datos bancarios. El pago se acreditará cuando se confirme la transferencia.'
        );
    }

    public function consultar(Pago $pago, ?string $referenciaExterna = null): ResultadoPasarela
    {
        return $pago->estado === 'aprobado'
            ? ResultadoPasarela::aprobado($pago->referencia_externa)
            : ResultadoPasarela::pendiente($pago->referencia_externa);
    }

    public function enSimulacion(): bool
    {
        return false;
    }
}
