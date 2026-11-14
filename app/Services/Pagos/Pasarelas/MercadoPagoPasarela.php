<?php

namespace App\Services\Pagos\Pasarelas;

use App\Models\Pago;
use App\Services\Pagos\ResultadoPasarela;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Integración con Mercado Pago - Checkout Pro.
 *
 * Flujo:
 *  1. Se crea una "preferencia" (POST /checkout/preferences) con el importe,
 *     la referencia externa (código del pago) y las URLs de retorno/notificación.
 *  2. El cliente paga en el checkout de Mercado Pago (init_point), con dinero en
 *     cuenta, tarjeta o QR.
 *  3. Mercado Pago notifica al webhook y redirige a la URL de retorno.
 *     En ambos casos el sistema CONSULTA el pago (GET /v1/payments/{id}) y
 *     solo confía en el estado devuelto por la API.
 *
 * Sin MERCADOPAGO_ACCESS_TOKEN la pasarela trabaja en modo simulación.
 */
class MercadoPagoPasarela implements PasarelaPago
{
    public function enSimulacion(): bool
    {
        return blank(config('pagos.mercado_pago.access_token'));
    }

    public function iniciar(Pago $pago): ResultadoPasarela
    {
        if ($this->enSimulacion()) {
            return ResultadoPasarela::pendiente(null, route('pagos.simulador', $pago), ['modo' => 'simulacion']);
        }

        $orden = $pago->reparacion;

        $preferencia = [
            'items' => [[
                'id'          => 'ORDEN-' . $orden->id,
                'title'       => "Reparación {$orden->marca} {$orden->modelo} - Orden #{$orden->id}",
                'quantity'    => 1,
                'currency_id' => 'ARS',
                'unit_price'  => (float) $pago->monto_total,
            ]],
            'payer' => array_filter([
                'name'  => $orden->cliente?->nombre,
                'email' => $orden->cliente?->email,
            ]),
            'external_reference'   => $pago->codigo,
            'statement_descriptor' => 'LOREBEL',
            'back_urls' => [
                'success' => route('pagos.retorno', $pago),
                'pending' => route('pagos.retorno', $pago),
                'failure' => route('pagos.retorno', $pago),
            ],
            'auto_return'      => 'approved',
            'notification_url' => route('webhooks.mercadopago'),
        ];

        try {
            $respuesta = $this->cliente()->post('/checkout/preferences', $preferencia);
        } catch (Throwable $e) {
            Log::error('Mercado Pago: error de conexión', ['error' => $e->getMessage()]);
            return ResultadoPasarela::rechazado('No fue posible conectar con Mercado Pago. Intente nuevamente.');
        }

        if ($respuesta->failed()) {
            Log::error('Mercado Pago: error al crear preferencia', ['body' => $respuesta->json()]);
            return ResultadoPasarela::rechazado('Mercado Pago rechazó la solicitud de cobro.');
        }

        $datos = $respuesta->json();
        $url = config('pagos.mercado_pago.sandbox') ? ($datos['sandbox_init_point'] ?? $datos['init_point']) : $datos['init_point'];

        return ResultadoPasarela::pendiente($datos['id'] ?? null, $url, ['preference_id' => $datos['id'] ?? null]);
    }

    public function consultar(Pago $pago, ?string $referenciaExterna = null): ResultadoPasarela
    {
        if ($this->enSimulacion() || blank($referenciaExterna)) {
            return ResultadoPasarela::pendiente($pago->referencia_externa);
        }

        $datos = $this->obtenerPago($referenciaExterna);
        if ($datos === null) {
            return ResultadoPasarela::pendiente($referenciaExterna);
        }

        // Protección: el pago consultado debe corresponder a este cobro.
        if (($datos['external_reference'] ?? null) !== $pago->codigo) {
            return ResultadoPasarela::rechazado('El pago informado no corresponde a esta orden.', $referenciaExterna);
        }

        return self::mapearEstado($datos);
    }

    /** GET /v1/payments/{id}: datos de un pago en Mercado Pago. */
    public function obtenerPago(string $paymentId): ?array
    {
        try {
            $respuesta = $this->cliente()->get('/v1/payments/' . urlencode($paymentId));
            return $respuesta->successful() ? $respuesta->json() : null;
        } catch (Throwable $e) {
            Log::error('Mercado Pago: error al consultar pago', ['id' => $paymentId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /** Traduce el estado de Mercado Pago al estado interno. */
    public static function mapearEstado(array $datos): ResultadoPasarela
    {
        $id = isset($datos['id']) ? (string) $datos['id'] : null;
        $resumen = [
            'status'             => $datos['status'] ?? null,
            'status_detail'      => $datos['status_detail'] ?? null,
            'payment_method_id'  => $datos['payment_method_id'] ?? null,
            'installments'       => $datos['installments'] ?? null,
            'transaction_amount' => $datos['transaction_amount'] ?? null,
        ];

        return match ($datos['status'] ?? null) {
            'approved'                            => ResultadoPasarela::aprobado($id, $resumen),
            'rejected', 'cancelled', 'refunded',
            'charged_back'                        => ResultadoPasarela::rechazado('Pago ' . ($datos['status_detail'] ?? $datos['status']), $id, $resumen),
            default                               => ResultadoPasarela::pendiente($id, null, $resumen),
        };
    }

    /**
     * Valida la firma x-signature de las notificaciones (si hay secreto configurado).
     * Manifiesto: "id:{data.id};request-id:{x-request-id};ts:{ts};" firmado con HMAC-SHA256.
     */
    public function firmaValida(?string $xSignature, ?string $xRequestId, ?string $dataId): bool
    {
        $secreto = config('pagos.mercado_pago.webhook_secret');
        if (blank($secreto)) {
            return true; // sin secreto configurado no se valida (igualmente se consulta el pago a la API)
        }
        if (blank($xSignature)) {
            return false;
        }

        $partes = [];
        foreach (explode(',', $xSignature) as $parte) {
            [$clave, $valor] = array_pad(explode('=', trim($parte), 2), 2, null);
            $partes[$clave] = $valor;
        }

        $manifiesto = 'id:' . strtolower((string) $dataId) . ';request-id:' . $xRequestId . ';ts:' . ($partes['ts'] ?? '') . ';';
        $esperado = hash_hmac('sha256', $manifiesto, $secreto);

        return hash_equals($esperado, (string) ($partes['v1'] ?? ''));
    }

    private function cliente()
    {
        return Http::baseUrl(config('pagos.mercado_pago.base_url'))
            ->withToken(config('pagos.mercado_pago.access_token'))
            ->acceptJson()
            ->timeout(15);
    }
}
