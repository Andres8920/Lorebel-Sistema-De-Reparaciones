<?php

namespace App\Services\Pagos\Pasarelas;

use App\Models\Pago;
use App\Services\Pagos\ResultadoPasarela;

/**
 * Estrategia de cobro. Cada medio de pago implementa esta interfaz,
 * de modo que el PagoService no conoce los detalles de cada proveedor
 * y agregar un medio nuevo no requiere modificar la lógica de negocio.
 */
interface PasarelaPago
{
    /** Inicia el cobro. Puede aprobarlo en el acto, dejarlo pendiente o requerir redirección. */
    public function iniciar(Pago $pago): ResultadoPasarela;

    /** Consulta en el proveedor el estado actual de un pago (por su id externo). */
    public function consultar(Pago $pago, ?string $referenciaExterna = null): ResultadoPasarela;

    /** Indica si la pasarela opera en modo simulación (sin credenciales reales). */
    public function enSimulacion(): bool;
}
