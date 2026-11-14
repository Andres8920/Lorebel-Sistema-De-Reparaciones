@extends('layouts.app')

@section('title', 'Cobros')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Cobros</h1>
            <p class="text-muted mb-0">Pagos registrados sobre órdenes de reparación</p>
        </div>
        <a href="{{ route('reportes.recaudacion') }}" class="btn btn-outline-primary"><i class="bi bi-bar-chart-line me-1"></i> Reporte de recaudación</a>
    </div>

    @if($pendientesTransferencia)
        <div class="alert alert-warning">
            <i class="bi bi-hourglass-split me-2"></i>
            Hay <strong>{{ $pendientesTransferencia }}</strong> transferencia(s) pendiente(s) de confirmar.
            <a href="{{ route('pagos.index', ['estado' => 'pendiente', 'medio_pago' => 'transferencia']) }}" class="alert-link">Ver</a>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Medio</label>
                    <select name="medio_pago" class="form-select">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Pago::MEDIOS as $k => $v)
                            <option value="{{ $k }}" @selected(request('medio_pago') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Pago::ESTADOS as $k => $v)
                            <option value="{{ $k }}" @selected(request('estado') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label small">Desde</label><input type="date" name="desde" value="{{ request('desde') }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label small">Hasta</label><input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control"></div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-fill"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                    <a href="{{ route('pagos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr><th>#</th><th>Fecha</th><th>Orden / Cliente</th><th>Medio</th><th class="text-end">Total</th><th>Estado</th><th>Registró</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                        <tr>
                            <td>{{ $pago->id }}</td>
                            <td>{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('ordenes.show', $pago->reparacion_id) }}">#{{ $pago->reparacion_id }}</a> · {{ $pago->reparacion?->cliente?->nombre }}</td>
                            <td><i class="bi {{ $pago->medio_icono }} me-1"></i>{{ $pago->medio_texto }}{{ $pago->cuotas > 1 ? " ({$pago->cuotas}c)" : '' }}</td>
                            <td class="text-end">$ {{ number_format($pago->monto_total, 2, ',', '.') }}</td>
                            <td><span class="badge bg-{{ $pago->estado_color }}">{{ $pago->estado_texto }}</span></td>
                            <td class="small text-muted">{{ $pago->usuario?->name ?? 'Sistema' }}</td>
                            <td><a href="{{ route('pagos.show', $pago) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No hay cobros para los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $pagos->links() }}
</div>
@endsection
