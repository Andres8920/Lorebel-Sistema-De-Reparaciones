<?php

namespace App\Services\Pagos;

/**
 * Objeto de valor que devuelve cada pasarela al iniciar o consultar un pago.
 */
final class ResultadoPasarela
{
    public const APROBADO  = 'aprobado';
    public const PENDIENTE = 'pendiente';
    public const RECHAZADO = 'rechazado';

    private function __construct(
        public readonly string $estado,
        public readonly ?string $referencia = null,
        public readonly ?string $urlRedireccion = null,
        public readonly array $datos = [],
        public readonly ?string $mensaje = null,
    ) {
    }

    public static function aprobado(?string $referencia = null, array $datos = []): self
    {
        return new self(self::APROBADO, $referencia, null, $datos);
    }

    public static function pendiente(?string $referencia = null, ?string $url = null, array $datos = [], ?string $mensaje = null): self
    {
        return new self(self::PENDIENTE, $referencia, $url, $datos, $mensaje);
    }

    public static function rechazado(string $mensaje, ?string $referencia = null, array $datos = []): self
    {
        return new self(self::RECHAZADO, $referencia, null, $datos, $mensaje);
    }

    public function requiereRedireccion(): bool
    {
        return $this->urlRedireccion !== null;
    }
}
