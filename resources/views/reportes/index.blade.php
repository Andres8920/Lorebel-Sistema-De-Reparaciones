@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<div class="fade-in">
    <h1 class="h2 mb-1">Reportes e indicadores</h1>
    <p class="text-muted mb-4">Información consolidada para la toma de decisiones del taller</p>

    <div class="row g-4">
        <div class="col-md-6">
            <a href="{{ route('reportes.recaudacion') }}" class="card h-100 text-decoration-none text-reset">
                <div class="card-body">
                    <i class="bi bi-pie-chart fs-2 text-primary"></i>
                    <h5 class="mt-2">Recaudación por medio de pago</h5>
                    <p class="text-muted mb-0">Total cobrado, ticket promedio, participación de cada medio, evolución diaria y órdenes con saldo pendiente.</p>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('reportes.tecnicos') }}" class="card h-100 text-decoration-none text-reset">
                <div class="card-body">
                    <i class="bi bi-person-workspace fs-2 text-primary"></i>
                    <h5 class="mt-2">Productividad de técnicos</h5>
                    <p class="text-muted mb-0">Órdenes asignadas y terminadas, efectividad, tiempo promedio de reparación y facturación por técnico.</p>
                </div>
            </a>
        </div>
        @if(auth()->user()->esAdministrador())
            <div class="col-md-6">
                <a href="{{ route('auditoria.index') }}" class="card h-100 text-decoration-none text-reset">
                    <div class="card-body">
                        <i class="bi bi-clock-history fs-2 text-primary"></i>
                        <h5 class="mt-2">Auditoría: trazabilidad</h5>
                        <p class="text-muted mb-0">Altas, modificaciones y bajas de órdenes, cobros y clientes, con valores originales y nuevos.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('auditoria.sesiones') }}" class="card h-100 text-decoration-none text-reset">
                    <div class="card-body">
                        <i class="bi bi-door-open fs-2 text-primary"></i>
                        <h5 class="mt-2">Auditoría: sesiones</h5>
                        <p class="text-muted mb-0">Inicios y cierres de sesión e intentos fallidos por usuario, fecha e IP.</p>
                    </div>
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
