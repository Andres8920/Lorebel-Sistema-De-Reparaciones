<?php

namespace App\Services\Pagos;

use RuntimeException;

/**
 * Se lanza cuando una operación de cobro viola una regla de negocio.
 * El mensaje se muestra directamente al usuario.
 */
class ReglaNegocioException extends RuntimeException
{
}
