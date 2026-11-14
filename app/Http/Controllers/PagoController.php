<?php

namespace App\Http\Controllers;

use App\Http\Requests\PagoRequest;
use App\Models\Pago;
use App\Models\Reparacion;
use App\Services\Pagos\Pasarelas\PasarelaFactory;
use App\Services\Pagos\PagoService;
use App\Services\Pagos\ReglaNegocioException;
use App\Services\Pagos\ResultadoPasarela;
use Illuminate\Http\Request;

/**
 * Requerimiento CORE - Iteración 2: Registrar cobro de orden de reparación.
 */
class PagoController extends Controller
{
    public function __construct(
        private PagoService $servicio,
        private PasarelaFactory $pasarelas,
    ) {
    }

    /** Listado de cobros con filtros (caja). */
    public function index(Request $request)
    {
        $query = Pago::with(['reparacion.cliente', 'usuario'])->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('medio_pago')) {
            $query->where('medio_pago', $request->medio_pago);
        }
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        $pagos = $query->paginate(20)->withQueryString();
        $pendientesTransferencia = Pago::pendientes()->where('medio_pago', 'transferencia')->count();

        return view('pagos.index', compact('pagos', 'pendientesTransferencia'));
    }

    /** Formulario de cobro de una orden. */
    public function create(Reparacion $orden)
    {
        $orden->load(['cliente', 'pagos']);

        $maximo = $this->servicio->montoMaximoCobrable($orden);
        $bloqueo = null;
        try {
            $this->servicio->validarPago($orden, 'efectivo', max($maximo, 0.01));
        } catch (ReglaNegocioException $e) {
            $bloqueo = $e->getMessage();
        }

        return view('pagos.create', [
            'orden'      => $orden,
            'maximo'     => $maximo,
            'bloqueo'    => $bloqueo,
            'esSena'     => ! in_array($orden->estado, ['listo', 'entregado'], true),
            'recargos'   => config('pagos.recargos_cuotas'),
            'simulacion' => [
                'mercado_pago' => $this->pasarelas->para('mercado_pago')->enSimulacion(),
                'modo'         => $this->pasarelas->para('modo')->enSimulacion(),
            ],
        ]);
    }

    /** Registra el cobro e inicia el pago en la pasarela elegida. */
    public function store(PagoRequest $request, Reparacion $orden)
    {
        try {
            [$pago, $resultado] = $this->servicio->iniciarPago(
                $orden,
                $request->medio_pago,
                (float) $request->monto,
                (int) ($request->cuotas ?? 1),
                $request->referencia,
                $request->observaciones,
                $request->user(),
            );
        } catch (ReglaNegocioException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($resultado->estado === ResultadoPasarela::RECHAZADO) {
            return redirect()->route('ordenes.show', $orden)->with('error', $resultado->mensaje);
        }

        if ($resultado->requiereRedireccion()) {
            return redirect()->away($resultado->urlRedireccion);
        }

        if ($pago->medio_pago === 'transferencia') {
            return redirect()->route('pagos.show', $pago)
                ->with('success', 'Cobro por transferencia registrado. Queda pendiente de confirmación.');
        }

        return redirect()->route('ordenes.show', $orden)
            ->with('success', "Cobro de $ " . number_format((float) $pago->monto_total, 2, ',', '.') . " registrado con {$pago->medio_texto}.");
    }

    /** Detalle / comprobante de un pago. */
    public function show(Pago $pago)
    {
        $pago->load(['reparacion.cliente', 'usuario']);

        return view('pagos.show', [
            'pago'          => $pago,
            'transferencia' => config('pagos.transferencia'),
            'auditoria'     => $pago->auditorias()->with('usuario')->get(),
        ]);
    }

    /** Confirmación manual de transferencia. */
    public function confirmar(Request $request, Pago $pago)
    {
        $request->validate([
            'numero_operacion' => ['required', 'string', 'max:50'],
            'comprobante'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], [
            'numero_operacion.required' => 'Ingrese el número de operación de la transferencia.',
        ]);

        $ruta = $request->file('comprobante')?->store('pagos/comprobantes', 'public');

        try {
            $this->servicio->confirmarTransferencia($pago, $request->numero_operacion, $ruta);
        } catch (ReglaNegocioException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pagos.show', $pago)->with('success', 'Transferencia confirmada. El pago fue acreditado.');
    }

    public function anular(Request $request, Pago $pago)
    {
        $request->validate(['motivo' => ['required', 'string', 'max:255']], ['motivo.required' => 'Indique el motivo.']);

        try {
            $this->servicio->anular($pago, $request->motivo);
        } catch (ReglaNegocioException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pago anulado.');
    }

    public function reintegrar(Request $request, Pago $pago)
    {
        $request->validate(['motivo' => ['required', 'string', 'max:255']], ['motivo.required' => 'Indique el motivo.']);

        try {
            $this->servicio->reintegrar($pago, $request->motivo);
        } catch (ReglaNegocioException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pago reintegrado. Recuerde devolver el dinero al cliente por el mismo medio.');
    }

    /** URL de retorno desde Mercado Pago / MODO (back_urls). */
    public function retorno(Request $request, Pago $pago)
    {
        $referencia = $request->query('payment_id') ?? $request->query('collection_id');

        if ($referencia && $pago->estaPendiente()) {
            $resultado = $this->pasarelas->para($pago->medio_pago)->consultar($pago, (string) $referencia);
            $this->servicio->aplicarResultado($pago, $resultado);
            $pago->refresh();
        }

        $mensaje = match ($pago->estado) {
            'aprobado'  => ['success', 'El pago fue aprobado y acreditado en la orden.'],
            'rechazado' => ['error', 'El pago fue rechazado: ' . $pago->motivo_rechazo],
            default     => ['success', 'El pago quedó pendiente de acreditación. Se actualizará automáticamente al recibir la notificación.'],
        };

        return redirect()->route('ordenes.show', $pago->reparacion_id)->with(...$mensaje);
    }

    /** Checkout simulado de Mercado Pago / MODO (solo sin credenciales reales). */
    public function simulador(Pago $pago)
    {
        $this->verificarSimulacion($pago);
        $pago->load('reparacion.cliente');

        return view('pagos.simulador', compact('pago'));
    }

    public function procesarSimulacion(Request $request, Pago $pago)
    {
        $this->verificarSimulacion($pago);
        $request->validate(['resultado' => ['required', 'in:aprobado,rechazado,pendiente']]);

        $referencia = strtoupper(substr($pago->medio_pago, 0, 4)) . '-SIM-' . random_int(100000000, 999999999);

        $resultado = match ($request->resultado) {
            'aprobado'  => ResultadoPasarela::aprobado($referencia, ['simulado' => true]),
            'rechazado' => ResultadoPasarela::rechazado('Fondos insuficientes (simulado)', $referencia, ['simulado' => true]),
            default     => ResultadoPasarela::pendiente($referencia, null, ['simulado' => true]),
        };

        $this->servicio->aplicarResultado($pago, $resultado);

        return redirect()->route('pagos.retorno', $pago);
    }

    private function verificarSimulacion(Pago $pago): void
    {
        abort_unless(
            in_array($pago->medio_pago, ['mercado_pago', 'modo'], true)
                && $this->pasarelas->para($pago->medio_pago)->enSimulacion(),
            404
        );
        abort_unless($pago->estaPendiente(), 410, 'El pago ya fue procesado.');
    }
}
