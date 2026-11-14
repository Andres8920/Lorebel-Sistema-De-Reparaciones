<?php

namespace App\Services\Pagos\Pasarelas;

use InvalidArgumentException;

/**
 * Fábrica de estrategias de cobro: devuelve la pasarela que corresponde al medio de pago.
 */
class PasarelaFactory
{
    public function para(string $medioPago): PasarelaPago
    {
        return match ($medioPago) {
            'efectivo'                          => app(EfectivoPasarela::class),
            'transferencia'                     => app(TransferenciaPasarela::class),
            'mercado_pago'                      => app(MercadoPagoPasarela::class),
            'modo'                              => app(ModoPasarela::class),
            'tarjeta_debito', 'tarjeta_credito' => app(TarjetaPasarela::class),
            default => throw new InvalidArgumentException("Medio de pago no soportado: {$medioPago}"),
        };
    }
}
