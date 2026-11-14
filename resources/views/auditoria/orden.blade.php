@extends('layouts.app')

@section('title', 'Trazabilidad orden #' . $orden->id)

@push('styles')
<style>
    .linea { border-left: 3px solid var(--border-color); margin-left: .5rem; padding-left: 1.25rem; }
    .evento { position: relative; padding-bottom: 1.25rem; }
    .evento::before { content: ''; position: absolute; left: -1.72rem; top: .3rem; width: .8rem; height: .8rem; border-radius: 50%; background: var(--primary-color); border: 2px solid #fff; }
    @media print { nav, footer, .btn { display: none !important; } }
</style>
@endpush

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Trazabilidad · Orden #{{ $orden->id }}</h1>
            <p class="text-muted mb-0">{{ $orden->cliente?->nombre }} · {{ $orden->marca }} {{ $orden->modelo }} · estado actual: {{ $orden->estado_texto }}{{ $orden->trashed() ? ' (eliminada)' : '' }}</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i></button>
            @unless($orden->trashed())
                <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Orden</a>
            @endunless
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-flag me-2"></i> Estado original</div>
                <div class="card-body small">
                    @if($estadoOriginal)
                        @foreach(['estado', 'dispositivo', 'marca', 'modelo', 'problema_reportado', 'presupuesto', 'presupuesto_aprobado', 'tecnico_id', 'fecha_ingreso'] as $campo)
                            <div class="d-flex justify-content-between border-bottom py-1">
                                <span class="text-muted">{{ $campo }}</span>
                                <span class="text-end">{{ \Illuminate\Support\Str::limit(is_bool($estadoOriginal[$campo] ?? null) ? (($estadoOriginal[$campo]) ? 'sí' : 'no') : ($estadoOriginal[$campo] ?? '∅'), 40) }}</span>
                            </div>
                        @endforeach
                    @else
                        <span class="text-muted">La orden fue creada antes de habilitar la auditoría.</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-clock-history me-2"></i> Transformaciones ({{ $eventos->count() }})</div>
                <div class="card-body">
                    <div class="linea">
                        @forelse($eventos as $registro)
                            <div class="evento">
                                <div class="mb-1">
                                    <span class="badge bg-{{ $registro->accion_color }}">{{ ucfirst($registro->accion) }}</span>
                                    <strong>{{ $registro->entidad === 'Reparacion' ? 'Orden' : 'Pago #' . $registro->entidad_id }}</strong>
                                    <span class="text-muted small">· {{ $registro->created_at->format('d/m/Y H:i:s') }} · {{ $registro->usuario_nombre }} · {{ $registro->ip }}</span>
                                </div>
                                @include('auditoria._cambios')
                            </div>
                        @empty
                            <span class="text-muted">Sin eventos registrados.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
