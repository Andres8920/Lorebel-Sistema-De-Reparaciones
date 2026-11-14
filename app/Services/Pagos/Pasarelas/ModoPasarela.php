<?php

namespace App\Services\Pagos\Pasarelas;

use App\Models\Pago;
use App\Services\Pagos\ResultadoPasarela;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Integración con MODO (billetera de los bancos argentinos).
 *
 * Flujo de "intención de pago":
 *  1. Se obtiene un token de acceso con las credenciales del comercio.
 *  2. Se crea una intención de pago con el importe y la referencia externa.
 *     MODO devuelve un QR / deeplink que el cliente abre desde la app MODO
 *     o desde la app de su banco.
 *  3. MODO notifica el resultado al webhook; el sistema actualiza el pago.
 *
 * IMPORTANTE: las credenciales y la documentación definitiva de la API las
 * entrega MODO al adherir el comercio (o el procesador elegido). Por eso las
 * rutas son configurables en config/pagos.php. Sin credenciales, la pasarela
 * trabaja en modo simulación para demostrar el circuito completo.
 */
class ModoPasarela implements PasarelaPago
{
    public function enSimulacion(): bool
    {
        return blank(config('pagos.modo.client_id')) || blank(config('pagos.modo.base_url'));
    }

    public function iniciar(Pago $pago): ResultadoPasarela
    {
        if ($this->enSimulacion()) {
            return ResultadoPasarela::pendiente(null, route('pagos.simulador', $pago), ['modo' => 'simulacion']);
        }

        $orden = $pago->reparacion;

        try {
            $respuesta = Http::baseUrl(config('pagos.modo.base_url'))
                ->withToken($this->token())
                ->acceptJson()
                ->timeout(15)
                ->post(config('pagos.modo.ruta_intencion'), [
                    'description'     => "Reparación Orden #{$orden->id}",
                    'amount'          => (float) $pago->monto_total,
                    'currency'        => 'ARS',
                    'store_id'        => config('pagos.modo.store_id'),
                    'external_intention_id' => $pago->codigo,
                    'webhook_notification'  => route('webhooks.modo'),
                ]);
        } catch (Throwable $e) {
            Log::error('MODO: error de conexión', ['error' => $e->getMessage()]);
            return ResultadoPasarela::rechazado('No fue posible conectar con MODO. Intente nuevamente.');
        }

        if ($respuesta->failed()) {
            Log::error('MODO: error al crear intención de pago', ['body' => $respuesta->json()]);
            return ResultadoPasarela::rechazado('MODO rechazó la solicitud de cobro.');
        }

        $datos = $respuesta->json();

        return ResultadoPasarela::pendiente(
            $datos['id'] ?? null,
            $datos['deeplink'] ?? $datos['checkout_url'] ?? null,
            ['qr' => $datos['qr'] ?? null, 'id' => $datos['id'] ?? null]
        );
    }

    public function consultar(Pago $pago, ?string $referenciaExterna = null): ResultadoPasarela
    {
        return $pago->estado === 'aprobado'
            ? ResultadoPasarela::aprobado($pago->referencia_externa)
            : ResultadoPasarela::pendiente($pago->referencia_externa);
    }

    /** Traduce el estado notificado por MODO al estado interno. */
    public static function mapearEstado(array $datos): ResultadoPasarela
    {
        $id = isset($datos['id']) ? (string) $datos['id'] : null;

        return match (strtoupper((string) ($datos['status'] ?? ''))) {
            'ACCEPTED', 'APPROVED', 'PAID'           => ResultadoPasarela::aprobado($id, $datos),
            'REJECTED', 'CANCELLED', 'EXPIRED'       => ResultadoPasarela::rechazado('Pago MODO ' . strtolower($datos['status']), $id, $datos),
            default                                  => ResultadoPasarela::pendiente($id, null, $datos),
        };
    }

    /** Valida la firma HMAC del webhook de MODO (si hay secreto configurado). */
    public function firmaValida(string $cuerpo, ?string $firma): bool
    {
        $secreto = config('pagos.modo.webhook_secret');
        if (blank($secreto)) {
            return true;
        }
        return hash_equals(hash_hmac('sha256', $cuerpo, $secreto), (string) $firma);
    }

    private function token(): string
    {
        return Cache::remember('modo_access_token', now()->addMinutes(50), function () {
            $respuesta = Http::baseUrl(config('pagos.modo.base_url'))
                ->acceptJson()
                ->post(config('pagos.modo.ruta_token'), [
                    'client_id'     => config('pagos.modo.client_id'),
                    'client_secret' => config('pagos.modo.client_secret'),
                ])
                ->throw();

            return $respuesta->json('access_token');
        });
    }
}
