@extends('layouts.app')

@section('title', 'Órdenes de Reparación')

@section('content')
<div class="fade-in">
    <!-- Encabezado con acciones -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Órdenes de Reparación</h1>
            <p class="text-muted mb-0">Gestiona todas las órdenes de reparación del taller</p>
        </div>
        <a href="{{ route('ordenes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nueva Orden
        </a>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('ordenes.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small text-muted">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Cliente, marca, modelo, IMEI..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="ingresado" {{ request('estado') == 'ingresado' ? 'selected' : '' }}>Ingresado</option>
                        <option value="en_reparacion" {{ request('estado') == 'en_reparacion' ? 'selected' : '' }}>En Reparación</option>
                        <option value="esperando_repuesto" {{ request('estado') == 'esperando_repuesto' ? 'selected' : '' }}>Esperando Repuesto</option>
                        <option value="listo" {{ request('estado') == 'listo' ? 'selected' : '' }}>Listo</option>
                        <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                        <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Técnico</label>
                    <select name="tecnico_id" class="form-select">
                        <option value="">Todos los técnicos</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ request('tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                {{ $tecnico->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de órdenes -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Lista de Órdenes</span>
            <span class="badge bg-primary">{{ $ordenes->total() }} órdenes</span>
        </div>
        <div class="card-body p-0">
            @if($ordenes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="80">Orden #</th>
                                <th>Cliente</th>
                                <th>Equipo</th>
                                <th>Estado</th>
                                <th>Técnico</th>
                                <th>Presupuesto</th>
                                <th>Ingreso</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordenes as $orden)
                                <tr>
                                    <td>
                                        <a href="{{ route('ordenes.show', $orden) }}" class="text-decoration-none">
                                            <strong class="text-primary">#{{ $orden->id }}</strong>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>
                                                {{ optional($orden->cliente)->nombre ?? 'Sin cliente' }}
                                            </strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ optional($orden->cliente)->telefono ?? '—' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $orden->marca }} {{ $orden->modelo }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $orden->dispositivo }}</small>
                                            @if($orden->imei_serial)
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-upc-scan"></i> {{ $orden->imei_serial }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $orden->estado_color }}">
                                            {{ $orden->estado_texto }}
                                        </span>
                                        @if($orden->presupuesto_aprobado === false)
                                            <br>
                                            <small class="badge bg-danger mt-1">Pres. Rechazado</small>
                                        @elseif($orden->presupuesto_aprobado === null && $orden->presupuesto)
                                            <br>
                                            <small class="badge bg-warning mt-1">Pend. Aprobación</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($orden->tecnico)
                                            <small>{{ $orden->tecnico->nombre_completo }}</small>
                                        @else
                                            <small class="text-muted">Sin asignar</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($orden->presupuesto)
                                            <strong class="text-success">$ {{ number_format($orden->presupuesto, 2, ',', '.') }}</strong>
                                            @if($orden->presupuesto_aprobado === true)
                                                <br>
                                                <small class="text-success">
                                                    <i class="bi bi-check-circle-fill"></i> Aprobado
                                                </small>
                                            @endif
                                        @else
                                            <small class="text-muted">Sin presupuesto</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $orden->fecha_ingreso->format('d/m/Y') }}</small>
                                        @if($orden->fecha_estimada_entrega)
                                            <br>
                                            <small class="text-info">
                                                <i class="bi bi-calendar-event"></i>
                                                {{ $orden->fecha_estimada_entrega->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a
                                                href="{{ route('ordenes.show', $orden) }}"
                                                class="btn btn-outline-primary"
                                                title="Ver detalles"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a
                                                href="{{ route('ordenes.edit', $orden) }}"
                                                class="btn btn-outline-primary"
                                                title="Editar"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a
                                                href="{{ route('ordenes.pdf', $orden) }}"
                                                class="btn btn-outline-primary"
                                                title="Ver comprobante"
                                                target="_blank"
                                            >
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: var(--border-color);"></i>
                    <p class="text-muted mt-3 mb-0">
                        @if(request('search') || request('estado') || request('tecnico_id'))
                            No se encontraron órdenes con los filtros aplicados.
                        @else
                            No hay órdenes registradas aún.
                        @endif
                    </p>
                    @if(request('search') || request('estado') || request('tecnico_id'))
                        <a href="{{ route('ordenes.index') }}" class="btn btn-outline-primary mt-3">
                            Limpiar filtros
                        </a>
                    @else
                        <a href="{{ route('ordenes.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-1"></i> Crear primera orden
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($ordenes->hasPages())
            <div class="card-footer bg-white">
                {{ $ordenes->appends(request()->except('page'))->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
