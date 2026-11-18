@extends('layouts.app')

@section('title', 'Orden #' . $orden->id)

@section('content')
<div class="fade-in">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Orden de Reparación #{{ $orden->id }}</h1>
            <p class="text-muted mb-0">Información completa de la orden</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ordenes.edit', $orden) }}" class="btn btn-primary me-1">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <a href="{{ route('ordenes.pdf', $orden) }}" class="btn btn-success me-1" target="_blank">
                <i class="bi bi-file-pdf me-1"></i> Comprobante PDF
            </a>
            <button
                type="button"
                class="btn btn-danger me-1"
                data-bs-toggle="modal"
                data-bs-target="#deleteModal"
            >
                <i class="bi bi-trash"></i>
            </button>
            <a href="{{ route('ordenes.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Columna izquierda -->
        <div class="col-lg-8">
            <!-- Estado y Cliente -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-3">Estado Actual</h5>
                            <h2 class="mb-0">
                                <span class="badge bg-{{ $orden->estado_color }} fs-5">
                                    {{ $orden->estado_texto }}
                                </span>
                            </h2>
                        </div>
                        <div class="col-md-6 border-start">
                            <h5 class="mb-3">Cliente</h5>
                            @if($orden->cliente)
                                <h4 class="mb-2">
                                    <a href="{{ route('clientes.show', $orden->cliente) }}" class="text-decoration-none">
                                        {{ $orden->cliente->nombre }}
                                    </a>
                                </h4>
                                <p class="mb-1">
                                    <i class="bi bi-telephone text-muted"></i>
                                    {{ $orden->cliente->telefono ?? '—' }}
                                </p>
                                @if(optional($orden->cliente)->whatsapp)
                                    <p class="mb-1">
                                        <i class="bi bi-whatsapp text-success"></i>
                                        {{ $orden->cliente->whatsapp }}
                                    </p>
                                @endif
                                @if(optional($orden->cliente)->email)
                                    <p class="mb-0">
                                        <i class="bi bi-envelope text-muted"></i>
                                        {{ $orden->cliente->email }}
                                    </p>
                                @endif
                            @else
                                <h4 class="mb-2">Sin cliente</h4>
                                <p class="mb-1 text-muted">—</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Equipo -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-phone me-2"></i> Información del Equipo
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted">Tipo de Dispositivo</label>
                            <p class="mb-0"><strong>{{ $orden->dispositivo }}</strong></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted">Marca</label>
                            <p class="mb-0"><strong>{{ $orden->marca }}</strong></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted">Modelo</label>
                            <p class="mb-0"><strong>{{ $orden->modelo }}</strong></p>
                        </div>
                        @if($orden->imei_serial)
                            <div class="col-12">
                                <label class="small text-muted">IMEI / Serial</label>
                                <p class="mb-0">
                                    <i class="bi bi-upc-scan"></i>
                                    <code>{{ $orden->imei_serial }}</code>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Problema y Diagnóstico -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-tools me-2"></i> Problema y Diagnóstico
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="small text-muted">Problema Reportado por el Cliente</label>
                        <div class="alert alert-warning mb-0">
                            {{ $orden->problema_reportado }}
                        </div>
                    </div>

                    @if($orden->diagnostico_tecnico)
                        <div class="mb-4">
                            <label class="small text-muted">Diagnóstico Técnico</label>
                            <div class="alert alert-info mb-0">
                                {{ $orden->diagnostico_tecnico }}
                            </div>
                        </div>
                    @endif

                    @if($orden->trabajo_realizado)
                        <div class="mb-0">
                            <label class="small text-muted">Trabajo Realizado</label>
                            <div class="alert alert-success mb-0">
                                {{ $orden->trabajo_realizado }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Fotos del Equipo -->
            @if($orden->fotos && count($orden->fotos) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-camera me-2"></i> Fotos del Equipo
                        <span class="badge bg-primary">{{ count($orden->fotos) }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($orden->fotos as $index => $foto)
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <img
                                            src="{{ Storage::url($foto) }}"
                                            class="img-thumbnail w-100"
                                            alt="Foto {{ $index + 1 }}"
                                            style="cursor: pointer;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#fotoModal{{ $index }}"
                                        >
                                    </div>

                                    <!-- Modal para ver foto en grande -->
                                    <div class="modal fade" id="fotoModal{{ $index }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Foto {{ $index + 1 }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <img
                                                        src="{{ Storage::url($foto) }}"
                                                        class="img-fluid"
                                                        alt="Foto {{ $index + 1 }}"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna derecha -->
        <div class="col-lg-4">
            @include('ordenes._pagos')

            <!-- Presupuesto -->
            @if($orden->presupuesto)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-currency-dollar me-2"></i> Presupuesto
                    </div>
                    <div class="card-body">
                        <h3 class="mb-3 text-success">
                            $ {{ number_format($orden->presupuesto, 2, ',', '.') }}
                        </h3>

                        <div class="mb-3">
                            <label class="small text-muted">Estado del Presupuesto</label>
                            <p class="mb-0">
                                <strong>{{ $orden->presupuesto_estado_texto }}</strong>
                                @if($orden->presupuesto_aprobado === true)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @elseif($orden->presupuesto_aprobado === false)
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                @else
                                    <i class="bi bi-clock text-warning"></i>
                                @endif
                            </p>
                        </div>

                        @if($orden->fecha_aprobacion_presupuesto)
                            <div class="mb-0">
                                <label class="small text-muted">Fecha de Aprobación</label>
                                <p class="mb-0">
                                    {{ $orden->fecha_aprobacion_presupuesto->format('d/m/Y') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Costos -->
            @if($orden->costo_repuestos > 0 || $orden->costo_mano_obra > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-receipt me-2"></i> Desglose de Costos
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Repuestos:</span>
                            <strong>$ {{ number_format($orden->costo_repuestos, 2, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Mano de Obra:</span>
                            <strong>$ {{ number_format($orden->costo_mano_obra, 2, ',', '.') }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <h5 class="mb-0 text-success">
                                $ {{ number_format($orden->costo_total, 2, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Técnico Asignado -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-gear me-2"></i> Técnico Asignado
                </div>
                <div class="card-body">
                    @if($orden->tecnico)
                        <h5 class="mb-2">{{ $orden->tecnico->nombre_completo }}</h5>
                        <p class="mb-1">
                            <i class="bi bi-telephone text-muted"></i>
                            {{ $orden->tecnico->telefono }}
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-envelope text-muted"></i>
                            {{ $orden->tecnico->email }}
                        </p>
                        @if($orden->tecnico->especialidad)
                            <span class="badge bg-info mt-2">{{ $orden->tecnico->especialidad }}</span>
                        @endif
                    @else
                        <p class="text-muted mb-0">
                            <i class="bi bi-exclamation-circle"></i> Sin técnico asignado
                        </p>
                    @endif
                </div>
            </div>

            <!-- Fechas -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-calendar-event me-2"></i> Fechas Importantes
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small text-muted">Fecha de Ingreso</label>
                        <p class="mb-0">
                            <i class="bi bi-calendar-check text-primary"></i>
                            {{ $orden->fecha_ingreso->format('d/m/Y') }}
                        </p>
                    </div>

                    @if($orden->fecha_estimada_entrega)
                        <div class="mb-3">
                            <label class="small text-muted">Entrega Estimada</label>
                            <p class="mb-0">
                                <i class="bi bi-calendar-event text-info"></i>
                                {{ $orden->fecha_estimada_entrega->format('d/m/Y') }}
                            </p>
                        </div>
                    @endif

                    @if($orden->fecha_entrega)
                        <div class="mb-0">
                            <label class="small text-muted">Fecha de Entrega</label>
                            <p class="mb-0">
                                <i class="bi bi-calendar-check text-success"></i>
                                {{ $orden->fecha_entrega->format('d/m/Y') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Información del Sistema -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i> Información del Sistema
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="small text-muted">Creado</label>
                        <p class="mb-0">{{ $orden->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="mb-0">
                        <label class="small text-muted">Última Actualización</label>
                        <p class="mb-0">{{ $orden->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar esta orden?</p>
                    <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Esta acción no se puede deshacer. Se eliminará la orden #{{ $orden->id }} de
                    <strong>{{ optional($orden->cliente)->nombre ?? 'Sin cliente' }}</strong>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('ordenes.destroy', $orden) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Eliminar Orden
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
