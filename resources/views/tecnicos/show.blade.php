@extends('layouts.app')

@section('title', 'Técnico: ' . $tecnico->nombre_completo)

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">{{ $tecnico->nombre_completo }}</h1>
            <p class="text-muted mb-0">Detalle del técnico</p>
        </div>
        <div>
            <a href="{{ route('tecnicos.edit', $tecnico) }}" class="btn btn-primary me-1">Editar</a>
            <a href="{{ route('tecnicos.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="small text-muted">Nombre Completo</label>
                    <p class="mb-2"><strong>{{ $tecnico->nombre_completo }}</strong></p>
                    <label class="small text-muted">Teléfono</label>
                    <p class="mb-2">{{ $tecnico->telefono ?? '—' }}</p>
                    <label class="small text-muted">Email</label>
                    <p class="mb-2">{{ $tecnico->email ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted">Especialidad</label>
                    <p class="mb-2">{{ $tecnico->especialidad ?? '—' }}</p>
                    <label class="small text-muted">Estado</label>
                    <p class="mb-2">{{ $tecnico->activo ? 'Activo' : 'Inactivo' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
