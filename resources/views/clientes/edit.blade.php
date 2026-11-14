@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="fade-in">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('clientes.index') }}">Clientes</a></li>
            <li class="breadcrumb-item"><a href="{{ route('clientes.show', $cliente) }}">{{ $cliente->nombre }}</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <!-- Título -->
    <div class="mb-4">
        <h1 class="h2 mb-1">Editar Cliente</h1>
        <p class="text-muted mb-0">Actualiza la información de {{ $cliente->nombre }}</p>
    </div>

    <!-- Formulario -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pencil-fill me-2"></i>
                    Información del Cliente
                </div>
                <div class="card-body">
                    <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('nombre') is-invalid @enderror"
                                id="nombre"
                                name="nombre"
                                value="{{ old('nombre', $cliente->nombre) }}"
                                required
                                autofocus
                            >
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $cliente->email) }}"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">
                                    Teléfono <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-telephone"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control @error('telefono') is-invalid @enderror"
                                        id="telefono"
                                        name="telefono"
                                        value="{{ old('telefono', $cliente->telefono) }}"
                                        required
                                    >
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- WhatsApp -->
                            <div class="col-md-6 mb-3">
                                <label for="whatsapp" class="form-label">
                                    WhatsApp
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-whatsapp"></i>
                                    </span>
                                    <input
                                        type="text"
                                        class="form-control @error('whatsapp') is-invalid @enderror"
                                        id="whatsapp"
                                        name="whatsapp"
                                        value="{{ old('whatsapp', $cliente->whatsapp) }}"
                                    >
                                    @error('whatsapp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Opcional si es diferente al teléfono</small>
                            </div>
                        </div>

                        <!-- DNI/CUIT -->
                        <div class="mb-3">
                            <label for="dni_cuit" class="form-label">
                                DNI / CUIT <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person-badge"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control @error('dni_cuit') is-invalid @enderror"
                                    id="dni_cuit"
                                    name="dni_cuit"
                                    value="{{ old('dni_cuit', $cliente->dni_cuit) }}"
                                    required
                                >
                                @error('dni_cuit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Notas Internas -->
                        <div class="mb-4">
                            <label for="notas_internas" class="form-label">
                                Notas Internas
                            </label>
                            <textarea
                                class="form-control @error('notas_internas') is-invalid @enderror"
                                id="notas_internas"
                                name="notas_internas"
                                rows="4"
                                placeholder="Información adicional de uso interno..."
                            >{{ old('notas_internas', $cliente->notas_internas) }}</textarea>
                            @error('notas_internas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Estas notas son privadas y no serán visibles para el cliente
                            </small>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Guardar Cambios
                            </button>
                            <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel lateral con información -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>
                    Información del Registro
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>ID:</strong> #{{ $cliente->id }}
                    </p>
                    <p class="mb-2">
                        <strong>Creado:</strong><br>
                        <small class="text-muted">{{ $cliente->created_at->format('d/m/Y H:i') }}</small>
                    </p>
                    <p class="mb-0">
                        <strong>Última actualización:</strong><br>
                        <small class="text-muted">{{ $cliente->updated_at->format('d/m/Y H:i') }}</small>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightbulb me-2"></i>
                    Recordatorio
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        El email debe ser único en el sistema
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        El DNI/CUIT debe ser único en el sistema
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
