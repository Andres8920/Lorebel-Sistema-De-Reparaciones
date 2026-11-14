@extends('layouts.app')

@section('title', 'Reporte de recaudación')

@push('styles')
<style>
    .kpi .valor { font-size: 1.55rem; font-weight: 700; }
    .kpi .etiqueta { color: var(--secondary-color); font-size: .9rem; }
    @media print { nav, footer, form, .btn { display: none !important; } }
</style>
@endpush

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Recaudación por medio de pago</h1>
            <p class="text-muted mb-0">Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }} · solo pagos aprobados</p>
        </div>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Reportes</a>
    </div>

    @include('reportes._filtro')

    @php $m = fn ($v) => '$ ' . number_format($v, 0, ',', '.'); @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card kpi h-100"><div class="card-body"><div class="etiqueta">Total cobrado</div><div class="valor">{{ $m($indicadores['total_cobrado']) }}</div><div class="small text-muted">{{ $indicadores['cantidad_cobros'] }} cobros</div></div></div></div>
        <div class="col-md-3"><div class="card kpi h-100"><div class="card-body"><div class="etiqueta">Ticket promedio</div><div class="valor">{{ $m($indicadores['ticket_promedio']) }}</div><div class="small text-muted">Medio principal: {{ $indicadores['medio_principal'] }}</div></div></div></div>
        <div class="col-md-3"><div class="card kpi h-100"><div class="card-body"><div class="etiqueta">Cobros digitales</div><div class="valor">{{ number_format($indicadores['porcentaje_digital'], 1, ',', '.') }} %</div><div class="small text-muted">MP, MODO y transferencias</div></div></div></div>
        <div class="col-md-3"><div class="card kpi h-100"><div class="card-body"><div class="etiqueta">Por cobrar</div><div class="valor text-danger">{{ $m($indicadores['por_cobrar']) }}</div><div class="small text-muted">{{ $m($indicadores['pendiente_acreditar']) }} pendiente de acreditar</div></div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">Participación por medio de pago</div>
                <div class="card-body"><canvas id="graficoMedios" height="260"></canvas></div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">Evolución diaria de la recaudación</div>
                <div class="card-body"><canvas id="graficoDias" height="260"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Detalle por medio</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Medio</th><th class="text-end">Cobros</th><th class="text-end">Total</th><th class="text-end">%</th></tr></thead>
                        <tbody>
                            @forelse($porMedio as $fila)
                                <tr>
                                    <td>{{ $fila->medio_texto }}</td>
                                    <td class="text-end">{{ $fila->cantidad }}</td>
                                    <td class="text-end">{{ $m($fila->total) }}</td>
                                    <td class="text-end">{{ $indicadores['total_cobrado'] ? number_format($fila->total * 100 / $indicadores['total_cobrado'], 1, ',', '.') : 0 }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Sin cobros en el período.</td></tr>
                            @endforelse
                        </tbody>
                        @if($indicadores['recargos'] > 0)
                            <tfoot><tr><td colspan="4" class="small text-muted">Incluye {{ $m($indicadores['recargos']) }} de recargos por cuotas.</td></tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Órdenes con saldo pendiente (listas o entregadas)</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Orden</th><th>Cliente</th><th>Estado</th><th class="text-end">Saldo</th></tr></thead>
                        <tbody>
                            @forelse($ordenesConSaldo->take(10) as $o)
                                <tr>
                                    <td><a href="{{ route('ordenes.show', $o) }}">#{{ $o->id }}</a></td>
                                    <td>{{ $o->cliente?->nombre }}</td>
                                    <td><span class="badge bg-{{ $o->estado_color }}">{{ $o->estado_texto }}</span></td>
                                    <td class="text-end text-danger">{{ $m($o->saldo) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No hay órdenes con saldo.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const colores = { 'Efectivo': '#10b981', 'Transferencia bancaria': '#2563eb', 'Mercado Pago': '#009ee3', 'MODO': '#00a650', 'Tarjeta de débito': '#f59e0b', 'Tarjeta de crédito': '#8b5cf6' };
    const medios = @json($porMedio->pluck('medio_texto'));
    const pesos = v => '$ ' + Number(v).toLocaleString('es-AR', { maximumFractionDigits: 0 });

    new Chart(document.getElementById('graficoMedios'), {
        type: 'doughnut',
        data: { labels: medios, datasets: [{ data: @json($porMedio->pluck('total')->map(fn ($v) => (float) $v)), backgroundColor: medios.map(m => colores[m] ?? '#64748b') }] },
        options: { plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: c => `${c.label}: ${pesos(c.raw)}` } } } }
    });

    new Chart(document.getElementById('graficoDias'), {
        type: 'bar',
        data: { labels: @json(array_keys($serie)), datasets: [{ label: 'Recaudación', data: @json(array_values($serie)), backgroundColor: '#2563eb', borderRadius: 4 }] },
        options: { plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => pesos(c.raw) } } }, scales: { y: { ticks: { callback: pesos } } } }
    });
</script>
@endpush
