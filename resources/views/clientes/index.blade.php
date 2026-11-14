@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="fade-in">
    <!-- Encabezado con acciones -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Gestión de Clientes</h1>
            <p class="text-muted mb-0">Administra la información de tus clientes</p>
        </div>
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Cliente
        </a>
    </div>

    <!-- Barra de búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('clientes.index') }}" method="GET" class="row g-3">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Buscar por nombre, email o DNI/CUIT..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de clientes -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Lista de Clientes</span>
            <span class="badge bg-primary">{{ $clientes->total() }} clientes</span>
        </div>
        <div class="card-body p-0">
            @if($clientes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>DNI/CUIT</th>
                                <th>Reparaciones</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">#{{ $cliente->id }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $cliente->nombre }}</strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-envelope text-muted me-1"></i>
                                        {{ $cliente->email }}
                                    </td>
                                    <td>
                                        <i class="bi bi-telephone text-muted me-1"></i>
                                        {{ $cliente->telefono }}
                                        @if($cliente->whatsapp)
                                            <br>
                                            <small class="text-success">
                                                <i class="bi bi-whatsapp"></i> {{ $cliente->whatsapp }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $cliente->dni_cuit }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $cliente->reparaciones->count() }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @include('components.action-buttons', [
                                            'resource' => 'clientes',
                                            'id' => $cliente->id,
                                            'showRoute' => route('clientes.show', $cliente),
                                            'editRoute' => route('clientes.edit', $cliente),
                                            'deleteRoute' => route('clientes.destroy', $cliente),
                                        ])
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
                        @if(request('search'))
                            No se encontraron clientes con el criterio de búsqueda.
                        @else
                            No hay clientes registrados aún.
                        @endif
                    </p>
                    @if(request('search'))
                        <a href="{{ route('clientes.index') }}" class="btn btn-outline-primary mt-3">
                            Ver todos los clientes
                        </a>
                    @else
                        <a href="{{ route('clientes.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-1"></i> Crear primer cliente
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($clientes->hasPages())
            <div class="card-footer bg-white">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmarEliminacion(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este cliente? Esta acción no se puede deshacer.')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush
