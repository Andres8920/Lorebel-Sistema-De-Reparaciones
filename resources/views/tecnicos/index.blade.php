@extends('layouts.app')

@section('title', 'Técnicos')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Técnicos</h1>
            <p class="text-muted mb-0">Gestiona los técnicos del taller</p>
        </div>
        <a href="{{ route('tecnicos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Técnico
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($tecnicos->count())
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Especialidad</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tecnicos as $t)
                                <tr>
                                    <td><strong>{{ $t->nombre_completo }}</strong></td>
                                    <td>
                                        <small class="text-muted">{{ $t->telefono ?? '—' }}</small><br>
                                        <small class="text-muted">{{ $t->email ?? '—' }}</small>
                                    </td>
                                    <td>{{ $t->especialidad ?? '—' }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('tecnicos.show', $t) }}" class="btn btn-outline-primary" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('tecnicos.edit', $t) }}" class="btn btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary action-delete"
                                                onclick="confirmarEliminacionTecnico({{ $t->id }})"
                                                title="Eliminar"
                                            >
                                                <i class="bi bi-trash text-danger"></i>
                                            </button>
                                        </div>

                                        <form
                                            id="delete-tecnico-{{ $t->id }}"
                                            action="{{ route('tecnicos.destroy', $t) }}"
                                            method="POST"
                                            class="d-none"
                                        >
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center text-muted">No hay técnicos registrados.</div>
            @endif
        </div>
        @if($tecnicos->hasPages())
            <div class="card-footer bg-white">
                {{ $tecnicos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmarEliminacionTecnico(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este técnico? Esta acción no se puede deshacer.')) {
            document.getElementById('delete-tecnico-' + id).submit();
        }
    }
</script>
@endpush
