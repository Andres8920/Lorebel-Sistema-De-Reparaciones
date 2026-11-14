{{-- Iteración 2: estado de cuenta y cobros de la orden --}}
<div class="card mb-4 border-primary-subtle">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash-stack me-2"></i> Cobros</span>
        @php
            $cobro = [
                'pagada' => ['success', 'Pagada'], 'parcial' => ['warning', 'Pago parcial'],
                'impaga' => ['danger', 'Impaga'], 'sin_importe' => ['secondary', 'Sin importe'],
            ][$orden->estado_cobro];
        @endphp
        <span class="badge bg-{{ $cobro[0] }}">{{ $cobro[1] }}</span>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between small"><span class="text-muted">Importe</span><span>$ {{ number_format($orden->importe_a_cobrar, 2, ',', '.') }}</span></div>
        <div class="d-flex justify-content-between small"><span class="text-muted">Cobrado</span><span class="text-success">$ {{ number_format($orden->total_pagado, 2, ',', '.') }}</span></div>
        @if($orden->total_pendiente_acreditacion > 0)
            <div class="d-flex justify-content-between small"><span class="text-muted">A acreditar</span><span class="text-warning">$ {{ number_format($orden->total_pendiente_acreditacion, 2, ',', '.') }}</span></div>
        @endif
        <div class="d-flex justify-content-between fw-bold border-top mt-2 pt-2"><span>Saldo</span><span>$ {{ number_format($orden->saldo, 2, ',', '.') }}</span></div>

        @if($orden->pagos->isNotEmpty())
            <ul class="list-unstyled small mt-3 mb-0">
                @foreach($orden->pagos as $pago)
                    <li class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <a href="{{ route('pagos.show', $pago) }}" class="text-decoration-none">
                            <i class="bi {{ $pago->medio_icono }} me-1"></i>{{ $pago->medio_texto }}
                            <span class="text-muted">· {{ $pago->created_at->format('d/m') }}</span>
                        </a>
                        <span>
                            $ {{ number_format($pago->monto_total, 2, ',', '.') }}
                            <span class="badge bg-{{ $pago->estado_color }} ms-1">{{ $pago->estado_texto }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if(auth()->user()->tieneRol('administrador', 'recepcion') && $orden->saldo > 0 && $orden->estado !== 'cancelado')
            <a href="{{ route('pagos.create', $orden) }}" class="btn btn-success w-100 mt-3">
                <i class="bi bi-cash-coin me-1"></i> {{ in_array($orden->estado, ['listo', 'entregado']) ? 'Cobrar' : 'Cobrar seña' }}
            </a>
        @endif
        @if(auth()->user()->esAdministrador())
            <a href="{{ route('auditoria.orden', $orden->id) }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">
                <i class="bi bi-clock-history me-1"></i> Ver trazabilidad
            </a>
        @endif
    </div>
</div>
