@extends('layouts.app')

@section('title', 'Productividad de técnicos')

@push('styles')
<style>@media print { nav, footer, form, .btn { display: none !important; } }</style>
@endpush

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Productividad de técnicos</h1>
            <p class="text-muted mb-0">Órdenes ingresadas del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Reportes</a>
    </div>

    @include('reportes._filtro')

    <div class="card mb-4">
        <div class="card-header">Órdenes asignadas vs. terminadas</div>
        <div class="card-body"><canvas id="graficoTecnicos" height="110"></canvas></div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Técnico</th><th class="text-end">Asignadas</th><th class="text-end">Terminadas</th><th class="text-end">En curso</th><th class="text-end">Efectividad</th><th class="text-end">Días prom. reparación</th><th class="text-end">Facturado</th></tr></thead>
                <tbody>
                    @forelse($filas as $f)
                        <tr>
                            <td>{{ $f->tecnico }}</td>
                            <td class="text-end">{{ $f->asignadas }}</td>
                            <td class="text-end">{{ $f->terminadas }}</td>
                            <td class="text-end">{{ $f->en_curso }}</td>
                            <td class="text-end">{{ number_format($f->efectividad, 0) }} %</td>
                            <td class="text-end">{{ $f->dias_promedio ?? '—' }}</td>
                            <td class="text-end">$ {{ number_format($f->facturado, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Sin órdenes asignadas en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('graficoTecnicos'), {
        type: 'bar',
        data: {
            labels: @json($filas->pluck('tecnico')),
            datasets: [
                { label: 'Asignadas', data: @json($filas->pluck('asignadas')), backgroundColor: '#93c5fd', borderRadius: 4 },
                { label: 'Terminadas', data: @json($filas->pluck('terminadas')), backgroundColor: '#2563eb', borderRadius: 4 },
            ]
        },
        options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
</script>
@endpush
