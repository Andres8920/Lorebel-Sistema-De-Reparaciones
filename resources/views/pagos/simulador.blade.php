<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout simulado - {{ $pago->medio_texto }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @php $esMp = $pago->medio_pago === 'mercado_pago'; @endphp
    <style>
        body { background: {{ $esMp ? '#ededed' : '#f2f7f4' }}; min-height: 100vh; font-family: -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .franja { background: {{ $esMp ? '#ffe600' : '#00a650' }}; color: {{ $esMp ? '#2d3277' : '#fff' }}; padding: 1rem 0; font-weight: 700; }
        .caja { max-width: 480px; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .aviso { background: #fff3cd; border: 1px dashed #d4a72c; font-size: .85rem; }
    </style>
</head>
<body>
<div class="franja"><div class="container">{{ $esMp ? 'Mercado Pago' : 'MODO' }} · Checkout</div></div>
<div class="container py-5 d-flex justify-content-center">
    <div class="caja p-4 w-100">
        <div class="aviso rounded p-2 mb-4">
            <i class="bi bi-cone-striped me-1"></i> <strong>Modo simulación.</strong> No hay credenciales configuradas en el .env;
            esta pantalla reemplaza al checkout real para demostrar el circuito. No se mueve dinero.
        </div>
        <p class="text-muted mb-1">Pagás a</p>
        <h5>Lorebel Servicio Técnico</h5>
        <p class="small text-muted">Orden #{{ $pago->reparacion_id }} · {{ $pago->reparacion?->marca }} {{ $pago->reparacion?->modelo }}</p>
        <div class="display-6 fw-semibold my-3">$ {{ number_format($pago->monto_total, 2, ',', '.') }}</div>

        @if(! $esMp)
            <div class="text-center my-3">
                <svg width="150" height="150" viewBox="0 0 21 21" role="img" aria-label="QR simulado">
                    @php mt_srand(crc32($pago->codigo)); @endphp
                    @for($y = 0; $y < 21; $y++) @for($x = 0; $x < 21; $x++)
                        @php $f = ($x < 7 && $y < 7) || ($x > 13 && $y < 7) || ($x < 7 && $y > 13); @endphp
                        @if($f ? (in_array($x % 14, [0, 6]) || in_array($y % 14, [0, 6]) || (($x % 14) > 1 && ($x % 14) < 5 && ($y % 14) > 1 && ($y % 14) < 5)) : mt_rand(0, 1))
                            <rect x="{{ $x }}" y="{{ $y }}" width="1" height="1" fill="#111"/>
                        @endif
                    @endfor @endfor
                </svg>
                <div class="small text-muted">Escaneá con la app MODO o la de tu banco</div>
            </div>
        @endif

        <form method="POST" action="{{ route('pagos.simulador.procesar', $pago) }}">
            @csrf
            <div class="d-grid gap-2">
                <button name="resultado" value="aprobado" class="btn btn-lg {{ $esMp ? 'btn-primary' : 'btn-success' }}">Pagar (aprobar)</button>
                <button name="resultado" value="pendiente" class="btn btn-outline-secondary">Dejar pendiente</button>
                <button name="resultado" value="rechazado" class="btn btn-outline-danger">Simular rechazo</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
