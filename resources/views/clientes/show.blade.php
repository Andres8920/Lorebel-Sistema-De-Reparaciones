@extends('layouts.app')

@section('title', 'Detalle del Cliente')

@section('content')
<div class="fade-in">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
            <li class="breadcrumb-item active">{{ $cliente->nombre }}</li>
        </ol>
    </nav>

    <!-- Encabezado con acciones -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">{{ $cliente->nombre }}</h1>
            <p class="text-muted mb-0">Cliente #{{ $cliente->id }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            <button
                type="button"
                class="btn btn-outline-primary action-delete"
                onclick="confirmDeletion('delete-clientes-{{ $cliente->id }}', 'Eliminar cliente', '¿Estás seguro de que deseas eliminar este cliente?\n\nEsta acción eliminará también todo el historial de reparaciones asociado y no se puede deshacer.')"
            >
                <i class="bi bi-trash text-danger"></i>
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Información del Cliente -->
        <div class="col-lg-8 mb-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-circle me-2"></i>
                    Información Personal
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">NOMBRE COMPLETO</label>
                            <p class="mb-0 fw-semibold">{{ $cliente->nombre }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">DNI / CUIT</label>
                            <p class="mb-0 fw-semibold">
                                <i class="bi bi-person-badge text-primary me-1"></i>
                                {{ $cliente->dni_cuit }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">EMAIL</label>
                            <p class="mb-0">
                                <i class="bi bi-envelope text-primary me-1"></i>
                                <a href="mailto:{{ $cliente->email }}">{{ $cliente->email }}</a>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">TELÉFONO</label>
                            <p class="mb-0">
                                <i class="bi bi-telephone text-primary me-1"></i>
                                <a href="tel:{{ $cliente->telefono }}">{{ $cliente->telefono }}</a>
                            </p>
                        </div>
                    </div>

                    @if($cliente->whatsapp)
                        <div class="row">
                            <div class="col-12">
                                <label class="text-muted small">WHATSAPP</label>
                                <p class="mb-0">
                                    <i class="bi bi-whatsapp text-success me-1"></i>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $cliente->whatsapp) }}" target="_blank">
                                        {{ $cliente->whatsapp }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($cliente->notas_internas)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <label class="text-muted small">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    NOTAS INTERNAS (PRIVADAS)
                                </label>
                                <div class="alert alert-info mb-0 mt-2">
                                    <i class="bi bi-info-circle me-2"></i>
                                    {{ $cliente->notas_internas }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial de Reparaciones -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-tools me-2"></i>
                        Historial de Reparaciones
                    </span>
                    <span class="badge bg-primary">
                        {{ $cliente->reparaciones->count() }} reparaciones
                    </span>
                </div>
                <div class="card-body">
                    @if($cliente->reparaciones->count() > 0)
                        <div class="timeline">
                            @foreach($cliente->historialReparaciones as $reparacion)
                                <div class="timeline-item mb-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="timeline-badge
                                                @if($reparacion->estado == 'entregada') bg-success
                                                @elseif($reparacion->estado == 'en_proceso') bg-primary
                                                @elseif($reparacion->estado == 'pendiente') bg-warning
                                                @elseif($reparacion->estado == 'terminada') bg-info
                                                @else bg-secondary
                                                @endif
                                            ">
                                                <i class="bi bi-tools"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                {{ $reparacion->dispositivo }}
                                                                @if($reparacion->marca)
                                                                    - {{ $reparacion->marca }}
                                                                @endif
                                                                @if($reparacion->modelo)
                                                                    {{ $reparacion->modelo }}
                                                                @endif
                                                            </h6>
                                                            <span class="badge
                                                                @if($reparacion->estado == 'entregada') bg-success
                                                                @elseif($reparacion->estado == 'en_proceso') bg-primary
                                                                @elseif($reparacion->estado == 'pendiente') bg-warning
                                                                @elseif($reparacion->estado == 'terminada') bg-info
                                                                @else bg-secondary
                                                                @endif
                                                            ">
                                                                {{ ucfirst(str_replace('_', ' ', $reparacion->estado)) }}
                                                            </span>
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $reparacion->fecha_ingreso->format('d/m/Y') }}
                                                        </small>
                                                    </div>
                                                    <p class="mb-2">
                                                        <strong>Problema:</strong> {{ $reparacion->problema_reportado }}
                                                    </p>
                                                    @if($reparacion->importe_a_cobrar > 0)
                                                        <p class="mb-0">
                                                            <strong>Costo:</strong>
                                                            <span class="text-success fw-bold">$ {{ number_format($reparacion->importe_a_cobrar, 2, ',', '.') }}</span>
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: var(--border-color);"></i>
                            <p class="text-muted mt-3 mb-0">
                                Este cliente no tiene reparaciones registradas aún.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="col-lg-4">
            <!-- Estadísticas rápidas -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-bar-chart me-2"></i>
                    Estadísticas
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Total Reparaciones</span>
                        <span class="badge bg-primary fs-6">{{ $cliente->reparaciones->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Entregadas</span>
                        <span class="badge bg-success fs-6">
                            {{ $cliente->reparaciones->where('estado', 'entregada')->count() }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">En Proceso</span>
                        <span class="badge bg-warning fs-6">
                            {{ $cliente->reparaciones->whereIn('estado', ['pendiente', 'en_proceso'])->count() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Información del sistema -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>
                    Información del Sistema
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Registrado:</strong><br>
                        <small class="text-muted">{{ $cliente->created_at->format('d/m/Y H:i') }}</small>
                    </p>
                    <p class="mb-0">
                        <strong>Última actualización:</strong><br>
                        <small class="text-muted">{{ $cliente->updated_at->format('d/m/Y H:i') }}</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de eliminación oculto -->
    <form
        id="delete-clientes-{{ $cliente->id }}"
        action="{{ route('clientes.destroy', $cliente) }}"
        method="POST"
        class="d-none"
    >
        @csrf
        @method('DELETE')
    </form>
</div>

@push('styles')
<style>
    .timeline-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }
</style>
@endpush

{{-- confirmation handled by components/action-buttons; no inline script here to avoid duplication --}}
@endsection
