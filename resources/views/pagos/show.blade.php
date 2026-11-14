@extends('layouts.app')

@section('title', 'Pago ' . $pago->id)

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Cobro #{{ $pago->id }}</h1>
            <p class="text-muted mb-0">Orden #{{ $pago->reparacion_id }} · {{ $pago->reparacion?->cliente?->nombre }}</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i> Imprimir</button>
            <a href="{{ route('ordenes.show', $pago->reparacion_id) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver a la orden</a>
        </div>
    </div>

    @include('partials.errores')

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi {{ $pago->medio_icono }} me-2"></i>{{ $pago->medio_texto }}</span>
                    <span class="badge bg-{{ $pago->estado_color }} fs-6">{{ $pago->estado_texto }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th class="text-muted fw-normal w-50">Monto imputado a la orden</th><td class="text-end">$ {{ number_format($pago->monto, 2, ',', '.') }}</td></tr>
                        @if($pago->recargo > 0)
                            <tr><th class="text-muted fw-normal">Recargo ({{ $pago->cuotas }} cuotas)</th><td class="text-end">$ {{ number_format($pago->recargo, 2, ',', '.') }}</td></tr>
                        @endif
                        <tr><th>Total abonado por el cliente</th><td class="text-end fw-bold fs-5">$ {{ number_format($pago->monto_total, 2, ',', '.') }}</td></tr>
                        <tr><th class="text-muted fw-normal">Referencia / Nº operación</th><td class="text-end">{{ $pago->referencia_externa ?? '—' }}</td></tr>
                        <tr><th class="text-muted fw-normal">Registrado</th><td class="text-end">{{ $pago->created_at->format('d/m/Y H:i') }} · {{ $pago->usuario?->name ?? 'Sistema' }}</td></tr>
                        <tr><th class="text-muted fw-normal">Acreditado</th><td class="text-end">{{ $pago->fecha_aprobacion?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                        @if($pago->motivo_rechazo)
                            <tr><th class="text-muted fw-normal">Motivo</th><td class="text-end text-danger">{{ $pago->motivo_rechazo }}</td></tr>
                        @endif
                        @if($pago->observaciones)
                            <tr><th class="text-muted fw-normal">Observaciones</th><td class="text-end">{{ $pago->observaciones }}</td></tr>
                        @endif
                        @if($pago->comprobante)
                            <tr><th class="text-muted fw-normal">Comprobante</th><td class="text-end"><a href="{{ asset('storage/' . $pago->comprobante) }}" target="_blank">Ver archivo</a></td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if($pago->estaPendiente() && $pago->medio_pago === 'transferencia' && auth()->user()->tieneRol('administrador', 'recepcion'))
                <div class="card mb-4 border-warning">
                    <div class="card-header"><i class="bi bi-bank me-2"></i> Confirmar transferencia</div>
                    <div class="card-body">
                        <p class="small text-muted">Datos informados al cliente:</p>
                        <ul class="small">
                            <li>Alias: <strong>{{ $transferencia['alias'] }}</strong></li>
                            <li>CBU: <strong>{{ $transferencia['cbu'] }}</strong></li>
                            <li>Titular: {{ $transferencia['titular'] }} ({{ $transferencia['banco'] }})</li>
                        </ul>
                        <form method="POST" action="{{ route('pagos.confirmar', $pago) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Nº de operación *</label>
                                <input type="text" name="numero_operacion" class="form-control" maxlength="50" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comprobante (opcional)</label>
                                <input type="file" name="comprobante" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <button class="btn btn-success w-100"><i class="bi bi-check2-circle me-1"></i> Confirmar acreditación</button>
                        </form>
                    </div>
                </div>
            @endif

            @if($pago->estaPendiente() && $pago->url_pago)
                <div class="card mb-4">
                    <div class="card-body">
                        <p class="small mb-2">Link de pago para enviar al cliente:</p>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" value="{{ $pago->url_pago }}" readonly id="link-pago">
                            <button class="btn btn-outline-primary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('link-pago').value)"><i class="bi bi-clipboard"></i></button>
                        </div>
                    </div>
                </div>
            @endif

            @if($pago->estaPendiente() && auth()->user()->tieneRol('administrador', 'recepcion'))
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="POST" action="{{ route('pagos.anular', $pago) }}">
                            @csrf
                            <label class="form-label small">Anular cobro pendiente</label>
                            <div class="input-group">
                                <input type="text" name="motivo" class="form-control" placeholder="Motivo" required maxlength="255">
                                <button class="btn btn-outline-danger">Anular</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if($pago->estado === 'aprobado' && auth()->user()->esAdministrador())
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="POST" action="{{ route('pagos.reintegrar', $pago) }}" onsubmit="return confirm('¿Confirma el reintegro de este pago?')">
                            @csrf
                            <label class="form-label small">Reintegrar pago al cliente (solo administrador)</label>
                            <div class="input-group">
                                <input type="text" name="motivo" class="form-control" placeholder="Motivo" required maxlength="255">
                                <button class="btn btn-outline-danger">Reintegrar</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><i class="bi bi-clock-history me-2"></i> Historial del cobro</div>
                <div class="card-body small">
                    @forelse($auditoria as $evento)
                        <div class="mb-2 pb-2 border-bottom">
                            <span class="badge bg-{{ $evento->accion_color }}">{{ ucfirst($evento->accion) }}</span>
                            <span class="text-muted">{{ $evento->created_at->format('d/m/Y H:i:s') }} · {{ $evento->usuario_nombre }}</span>
                            @if($evento->accion === 'modificacion')
                                @foreach($evento->campos as $campo)
                                    <div><code>{{ $campo }}</code>: {{ json_encode($evento->valores_anteriores[$campo] ?? null, JSON_UNESCAPED_UNICODE) }} → <strong>{{ json_encode($evento->valores_nuevos[$campo] ?? null, JSON_UNESCAPED_UNICODE) }}</strong></div>
                                @endforeach
                            @endif
                        </div>
                    @empty
                        <span class="text-muted">Sin registros.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
