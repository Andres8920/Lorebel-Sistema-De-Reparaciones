<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Services\Pagos\Pasarelas\MercadoPagoPasarela;
use App\Services\Pagos\Pasarelas\ModoPasarela;
use App\Services\Pagos\PagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Notificaciones asíncronas (webhooks) de las pasarelas de pago.
 * Rutas públicas, sin CSRF. Siempre responden 200 para evitar reintentos
 * infinitos; los errores quedan registrados en el log.
 */
class WebhookController extends Controller
{
    public function __construct(private PagoService $servicio)
    {
    }

    public function mercadoPago(Request $request, MercadoPagoPasarela $pasarela)
    {
        $tipo = $request->input('type') ?? $request->query('topic') ?? $request->query('type');
        $paymentId = $request->input('data.id') ?? $request->query('data_id') ?? $request->query('id');

        if ($tipo !== 'payment' || blank($paymentId)) {
            return response()->json(['ok' => true, 'ignorado' => true]);
        }

        if (! $pasarela->firmaValida($request->header('x-signature'), $request->header('x-request-id'), (string) $paymentId)) {
            Log::warning('Mercado Pago: firma de webhook inválida', ['id' => $paymentId]);
            return response()->json(['ok' => false], 401);
        }

        // Nunca se confía en el cuerpo de la notificación: se consulta el pago a la API.
        $datos = $pasarela->obtenerPago((string) $paymentId);
        $pago = $datos ? Pago::where('codigo', $datos['external_reference'] ?? '')->first() : null;

        if (! $pago) {
            Log::warning('Mercado Pago: pago no encontrado', ['id' => $paymentId]);
            return response()->json(['ok' => true]);
        }

        $this->servicio->aplicarResultado($pago, MercadoPagoPasarela::mapearEstado($datos));

        return response()->json(['ok' => true]);
    }

    public function modo(Request $request, ModoPasarela $pasarela)
    {
        if (! $pasarela->firmaValida($request->getContent(), $request->header('x-signature'))) {
            Log::warning('MODO: firma de webhook inválida');
            return response()->json(['ok' => false], 401);
        }

        $codigo = $request->input('external_intention_id') ?? $request->input('external_reference');
        $pago = Pago::where('codigo', $codigo)->where('medio_pago', 'modo')->first();

        if (! $pago) {
            Log::warning('MODO: pago no encontrado', ['codigo' => $codigo]);
            return response()->json(['ok' => true]);
        }

        $this->servicio->aplicarResultado($pago, ModoPasarela::mapearEstado($request->all()));

        return response()->json(['ok' => true]);
    }
}
