@extends('layouts.app')

@section('title', 'Cobrar orden #' . $orden->id)

@push('styles')
<style>
    .medio-opcion { cursor: pointer; border: 2px solid var(--border-color); border-radius: 12px; padding: 1rem; height: 100%; transition: border-color .15s, background-color .15s; background: #fff; }
    .medio-opcion:hover { border-color: #93c5fd; }
    .btn-check:checked + .medio-opcion { border-color: var(--primary-color); background: #eff6ff; }
    .medio-opcion i { font-size: 1.6rem; }
    .resumen-linea { display: flex; justify-content: space-between; padding: .35rem 0; }
    .resumen-total { border-top: 2px solid var(--border-color); margin-top: .5rem; padding-top: .75rem; font-size: 1.25rem; font-weight: 700; }
    .icono-mp { color: #009ee3; } .icono-modo { color: #00a650; }
</style>
@endpush

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Registrar cobro</h1>
            <p class="text-muted mb-0">Orden #{{ $orden->id }} · {{ $orden->cliente?->nombre ?? 'Sin cliente' }} · {{ $orden->marca }} {{ $orden->modelo }}</p>
        </div>
        <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver a la orden</a>
    </div>

    @include('partials.errores')

    <div class="row g-4">
        <div class="col-lg-8">
            @if($bloqueo)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>No es posible cobrar esta orden.</strong> {{ $bloqueo }}
                </div>
            @else
                @if($esSena)
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        El equipo todavía no está listo: solo se puede cobrar una <strong>seña</strong> de hasta el
                        {{ (int) config('pagos.sena_maxima_porcentaje') }}&nbsp;% del importe.
                    </div>
                @endif

                <form method="POST" action="{{ route('pagos.store', $orden) }}" id="form-cobro" novalidate>
                    @csrf
                    <div class="card mb-4">
                        <div class="card-header"><i class="bi bi-wallet2 me-2"></i> Medio de pago</div>
                        <div class="card-body">
                            <div class="row g-3">
                                @php
                                    $iconos = [
                                        'efectivo' => ['bi-cash-coin text-success', 'Se acredita en el momento'],
                                        'transferencia' => ['bi-bank text-primary', 'CBU / alias · requiere confirmación'],
                                        'mercado_pago' => ['bi-wallet2 icono-mp', 'Link de pago, QR o tarjeta'],
                                        'modo' => ['bi-phone icono-modo', 'Pago desde la app del banco'],
                                        'tarjeta_debito' => ['bi-credit-card text-secondary', 'Terminal del local'],
                                        'tarjeta_credito' => ['bi-credit-card-2-front text-secondary', 'Terminal · 1, 3 o 6 cuotas'],
                                    ];
                                @endphp
                                @foreach(\App\Models\Pago::MEDIOS as $clave => $texto)
                                    <div class="col-md-4 col-6">
                                        <input type="radio" class="btn-check" name="medio_pago" id="medio-{{ $clave }}" value="{{ $clave }}"
                                               {{ old('medio_pago', 'efectivo') === $clave ? 'checked' : '' }}>
                                        <label class="medio-opcion d-block" for="medio-{{ $clave }}">
                                            <i class="bi {{ $iconos[$clave][0] }}"></i>
                                            <div class="fw-semibold mt-2">{{ $texto }}</div>
                                            <div class="small text-muted">{{ $iconos[$clave][1] }}</div>
                                            @if(($simulacion[$clave] ?? false))
                                                <span class="badge bg-light text-dark border mt-2">Modo simulación</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><i class="bi bi-currency-dollar me-2"></i> Importe</div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="monto" class="form-label">Monto a cobrar *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0.01" max="{{ $maximo }}" name="monto" id="monto"
                                               class="form-control @error('monto') is-invalid @enderror"
                                               value="{{ old('monto', number_format($maximo, 2, '.', '')) }}" required>
                                    </div>
                                    <div class="form-text">Máximo cobrable ahora: $ {{ number_format($maximo, 2, ',', '.') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('monto').value='{{ number_format($maximo, 2, '.', '') }}'; actualizar();">
                                        {{ $esSena ? 'Seña máxima' : 'Saldo total' }}
                                    </button>
                                </div>

                                <div class="col-md-6" id="grupo-cuotas">
                                    <label for="cuotas" class="form-label">Cuotas</label>
                                    <select name="cuotas" id="cuotas" class="form-select">
                                        @foreach($recargos as $cant => $pct)
                                            <option value="{{ $cant }}" data-recargo="{{ $pct }}" {{ (int) old('cuotas', 1) === $cant ? 'selected' : '' }}>
                                                {{ $cant }} {{ $cant === 1 ? 'pago' : 'cuotas' }} {{ $pct ? "(recargo {$pct} %)" : '(sin recargo)' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6" id="grupo-referencia">
                                    <label for="referencia" class="form-label">Nº de cupón de la terminal *</label>
                                    <input type="text" name="referencia" id="referencia" maxlength="50"
                                           class="form-control @error('referencia') is-invalid @enderror" value="{{ old('referencia') }}">
                                </div>
                                <div class="col-12">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <input type="text" name="observaciones" id="observaciones" maxlength="500" class="form-control" value="{{ old('observaciones') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 d-none" id="info-transferencia">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="bi bi-bank me-2"></i>Datos para informar al cliente</h6>
                            <div class="row small">
                                @foreach(config('pagos.transferencia') as $campo => $valor)
                                    <div class="col-md-6 mb-1"><span class="text-muted text-capitalize">{{ $campo }}:</span> <strong>{{ $valor }}</strong></div>
                                @endforeach
                            </div>
                            <p class="small text-muted mb-0 mt-2">El cobro queda <strong>pendiente</strong> hasta que se verifique la acreditación y se confirme con el número de operación.</p>
                        </div>
                    </div>

                    <div class="card mb-4 d-none" id="info-online">
                        <div class="card-body small">
                            <i class="bi bi-box-arrow-up-right me-2"></i>
                            Al confirmar, se generará el pago en <strong id="nombre-pasarela"></strong> y se abrirá el checkout para que el cliente pague.
                            El cobro se acredita automáticamente cuando la pasarela lo aprueba.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success" id="btn-cobrar"><i class="bi bi-check2-circle me-1"></i> Confirmar cobro</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-receipt me-2"></i> Estado de cuenta</div>
                <div class="card-body">
                    <div class="resumen-linea"><span class="text-muted">Importe de la orden</span><span>$ {{ number_format($orden->importe_a_cobrar, 2, ',', '.') }}</span></div>
                    <div class="resumen-linea"><span class="text-muted">Cobrado</span><span class="text-success">− $ {{ number_format($orden->total_pagado, 2, ',', '.') }}</span></div>
                    @if($orden->total_pendiente_acreditacion > 0)
                        <div class="resumen-linea"><span class="text-muted">Pendiente de acreditar</span><span class="text-warning">$ {{ number_format($orden->total_pendiente_acreditacion, 2, ',', '.') }}</span></div>
                    @endif
                    <div class="resumen-linea resumen-total"><span>Saldo</span><span>$ {{ number_format($orden->saldo, 2, ',', '.') }}</span></div>
                </div>
            </div>

            @unless($bloqueo)
                <div class="card">
                    <div class="card-header"><i class="bi bi-calculator me-2"></i> Este cobro</div>
                    <div class="card-body">
                        <div class="resumen-linea"><span class="text-muted">Monto</span><span id="r-monto">—</span></div>
                        <div class="resumen-linea"><span class="text-muted">Recargo financiero</span><span id="r-recargo">$ 0,00</span></div>
                        <div class="resumen-linea resumen-total"><span>Paga el cliente</span><span id="r-total">—</span></div>
                        <div class="small text-muted mt-2" id="r-cuota"></div>
                    </div>
                </div>
            @endunless
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const fmt = n => '$ ' + n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function actualizar() {
        const medio = document.querySelector('input[name="medio_pago"]:checked')?.value;
        if (!medio) return;
        const esCredito = medio === 'tarjeta_credito';
        const esTarjeta = medio.startsWith('tarjeta');

        document.getElementById('grupo-cuotas').classList.toggle('d-none', !esCredito);
        document.getElementById('grupo-referencia').classList.toggle('d-none', !esTarjeta);
        document.getElementById('info-transferencia').classList.toggle('d-none', medio !== 'transferencia');
        document.getElementById('info-online').classList.toggle('d-none', !['mercado_pago', 'modo'].includes(medio));
        document.getElementById('nombre-pasarela').textContent = medio === 'modo' ? 'MODO' : 'Mercado Pago';

        const monto = parseFloat(document.getElementById('monto').value) || 0;
        const sel = document.getElementById('cuotas');
        const cuotas = esCredito ? parseInt(sel.value) : 1;
        const pct = esCredito ? parseFloat(sel.selectedOptions[0].dataset.recargo) : 0;
        const recargo = Math.round(monto * pct) / 100;
        const total = monto + recargo;

        document.getElementById('r-monto').textContent = fmt(monto);
        document.getElementById('r-recargo').textContent = fmt(recargo);
        document.getElementById('r-total').textContent = fmt(total);
        document.getElementById('r-cuota').textContent = cuotas > 1 ? `${cuotas} cuotas de ${fmt(total / cuotas)}` : '';
    }

    document.querySelectorAll('input[name="medio_pago"], #cuotas, #monto').forEach(el => {
        el.addEventListener('change', actualizar);
        el.addEventListener('input', actualizar);
    });
    document.getElementById('form-cobro')?.addEventListener('submit', () => {
        const b = document.getElementById('btn-cobrar');
        b.disabled = true; b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando…';
    });
    actualizar();
</script>
@endpush
