@extends('layouts.app')

@section('title', 'Auditoría - Trazabilidad')

@section('content')
<div class="fade-in">
    <h1 class="h2 mb-1">Auditoría · Trazabilidad</h1>
    <p class="text-muted mb-4">Quién hizo qué, cuándo, y cuáles eran los valores originales</p>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Entidad</label>
                    <select name="entidad" class="form-select">
                        <option value="">Todas</option>
                        @foreach(['Reparacion' => 'Orden', 'Pago' => 'Pago', 'Cliente' => 'Cliente'] as $k => $t)
                            <option value="{{ $k }}" @selected(request('entidad') === $k)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label small">ID</label><input type="number" name="entidad_id" value="{{ request('entidad_id') }}" class="form-control"></div>
                <div class="col-md-2">
                    <label class="form-label small">Acción</label>
                    <select name="accion" class="form-select">
                        <option value="">Todas</option>
                        @foreach(['alta', 'modificacion', 'baja', 'restauracion'] as $a)
                            <option value="{{ $a }}" @selected(request('accion') === $a)>{{ ucfirst($a) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Usuario</label>
                    <select name="user_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label small">Desde</label><input type="date" name="desde" value="{{ request('desde') }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label small">Hasta</label><input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control"></div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-top">
                <thead><tr><th>Fecha y hora</th><th>Usuario</th><th>Acción</th><th>Elemento</th><th>Cambios</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse($registros as $registro)
                        <tr>
                            <td class="text-nowrap small">{{ $registro->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="small">{{ $registro->usuario_nombre }}</td>
                            <td><span class="badge bg-{{ $registro->accion_color }}">{{ ucfirst($registro->accion) }}</span></td>
                            <td class="small text-nowrap">
                                @if($registro->entidad === 'Reparacion')
                                    <a href="{{ route('auditoria.orden', $registro->entidad_id) }}">Orden #{{ $registro->entidad_id }}</a>
                                @else
                                    {{ $registro->entidad }} #{{ $registro->entidad_id }}
                                @endif
                            </td>
                            <td>@include('auditoria._cambios')</td>
                            <td class="small text-muted">{{ $registro->ip }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $registros->links() }}
</div>
@endsection
