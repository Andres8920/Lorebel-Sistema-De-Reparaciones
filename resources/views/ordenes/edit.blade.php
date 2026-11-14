@extends('layouts.app')

@section('title', 'Editar Orden #' . $orden->id)

@section('content')
<div class="fade-in">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Editar Orden #{{ $orden->id }}</h1>
            <p class="text-muted mb-0">Actualiza la información de la orden</p>
        </div>
        <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <form action="{{ route('ordenes.update', $orden) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-lg-8">
                <!-- Información del Cliente y Equipo -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-person-circle me-2"></i> Cliente y Equipo
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cliente_search" class="form-label">
                                    Cliente <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input
                                        type="text"
                                        id="cliente_search"
                                        class="form-control @error('cliente_id') is-invalid @enderror"
                                        placeholder="Buscar por nombre, teléfono, email..."
                                        autocomplete="off"
                                        value="{{ optional($orden->cliente)->nombre }}{{ optional($orden->cliente)->telefono ? ' - ' . $orden->cliente->telefono : '' }}"
                                    >
                                    <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id', $orden->cliente_id) }}">
                                    <div id="cliente_results" class="autocomplete-dropdown"></div>
                                </div>
                                @error('cliente_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="dispositivo" class="form-label">
                                    Tipo de Dispositivo <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="dispositivo"
                                    id="dispositivo"
                                    class="form-control @error('dispositivo') is-invalid @enderror"
                                    value="{{ old('dispositivo', $orden->dispositivo) }}"
                                    placeholder="Ej: Smartphone, Notebook, Tablet..."
                                    required
                                >
                                @error('dispositivo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="marca" class="form-label">
                                    Marca <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="marca"
                                    id="marca"
                                    class="form-control @error('marca') is-invalid @enderror"
                                    value="{{ old('marca', $orden->marca) }}"
                                    placeholder="Ej: Samsung, Apple..."
                                    required
                                >
                                @error('marca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="modelo" class="form-label">
                                    Modelo <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="modelo"
                                    id="modelo"
                                    class="form-control @error('modelo') is-invalid @enderror"
                                    value="{{ old('modelo', $orden->modelo) }}"
                                    placeholder="Ej: Galaxy S23, iPhone 14..."
                                    required
                                >
                                @error('modelo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="imei_serial" class="form-label">
                                    IMEI / Serial
                                </label>
                                <input
                                    type="text"
                                    name="imei_serial"
                                    id="imei_serial"
                                    class="form-control @error('imei_serial') is-invalid @enderror"
                                    value="{{ old('imei_serial', $orden->imei_serial) }}"
                                    placeholder="Identificador único..."
                                >
                                @error('imei_serial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Problema y Diagnóstico -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-tools me-2"></i> Problema y Diagnóstico
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="problema_reportado" class="form-label">
                                Problema Reportado <span class="text-danger">*</span>
                            </label>
                            <textarea
                                name="problema_reportado"
                                id="problema_reportado"
                                class="form-control @error('problema_reportado') is-invalid @enderror"
                                rows="3"
                                placeholder="Describe el problema que reporta el cliente..."
                                required
                            >{{ old('problema_reportado', $orden->problema_reportado) }}</textarea>
                            @error('problema_reportado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="diagnostico_tecnico" class="form-label">
                                Diagnóstico Técnico
                            </label>
                            <textarea
                                name="diagnostico_tecnico"
                                id="diagnostico_tecnico"
                                class="form-control @error('diagnostico_tecnico') is-invalid @enderror"
                                rows="3"
                                placeholder="Diagnóstico del técnico..."
                            >{{ old('diagnostico_tecnico', $orden->diagnostico_tecnico) }}</textarea>
                            @error('diagnostico_tecnico')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="trabajo_realizado" class="form-label">
                                Trabajo Realizado
                            </label>
                            <textarea
                                name="trabajo_realizado"
                                id="trabajo_realizado"
                                class="form-control @error('trabajo_realizado') is-invalid @enderror"
                                rows="3"
                                placeholder="Detalle del trabajo realizado..."
                            >{{ old('trabajo_realizado', $orden->trabajo_realizado) }}</textarea>
                            @error('trabajo_realizado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Fotos del Equipo -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-camera me-2"></i> Fotos del Equipo
                    </div>
                    <div class="card-body">
                        @if($orden->fotos && count($orden->fotos) > 0)
                            <div class="mb-3">
                                <label class="form-label">Fotos actuales</label>
                                <div class="row g-2" id="fotos-actuales">
                                    @foreach($orden->fotos as $index => $foto)
                                        <div class="col-3" id="foto-{{ $index }}">
                                            <div class="position-relative">
                                                <img src="{{ Storage::url($foto) }}" class="img-thumbnail w-100" alt="Foto {{ $index + 1 }}">
                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                                    onclick="eliminarFoto({{ $orden->id }}, {{ $index }})"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="fotos" class="form-label">
                                Agregar nuevas fotos
                            </label>
                            <input
                                type="file"
                                name="fotos[]"
                                id="fotos"
                                class="form-control @error('fotos') is-invalid @enderror @error('fotos.*') is-invalid @enderror"
                                accept="image/*"
                                multiple
                            >
                            <div class="form-text">
                                Puedes seleccionar múltiples archivos. Máximo 2MB por imagen.
                            </div>
                            @error('fotos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('fotos.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="preview-fotos" class="row g-2"></div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="col-lg-4">
                <!-- Estado y Asignación -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-gear me-2"></i> Estado y Asignación
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="estado" class="form-label">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select
                                name="estado"
                                id="estado"
                                class="form-select @error('estado') is-invalid @enderror"
                                required
                            >
                                <option value="ingresado" {{ old('estado', $orden->estado) == 'ingresado' ? 'selected' : '' }}>
                                    Ingresado
                                </option>
                                <option value="en_reparacion" {{ old('estado', $orden->estado) == 'en_reparacion' ? 'selected' : '' }}>
                                    En Reparación
                                </option>
                                <option value="esperando_repuesto" {{ old('estado', $orden->estado) == 'esperando_repuesto' ? 'selected' : '' }}>
                                    Esperando Repuesto
                                </option>
                                <option value="listo" {{ old('estado', $orden->estado) == 'listo' ? 'selected' : '' }}>
                                    Listo
                                </option>
                                <option value="entregado" {{ old('estado', $orden->estado) == 'entregado' ? 'selected' : '' }}>
                                    Entregado
                                </option>
                                <option value="cancelado" {{ old('estado', $orden->estado) == 'cancelado' ? 'selected' : '' }}>
                                    Cancelado
                                </option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="tecnico_id" class="form-label">
                                Técnico Asignado
                            </label>
                            <select
                                name="tecnico_id"
                                id="tecnico_id"
                                class="form-select @error('tecnico_id') is-invalid @enderror"
                            >
                                <option value="">Sin asignar</option>
                                @foreach($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $orden->tecnico_id) == $tecnico->id ? 'selected' : '' }}>
                                        {{ $tecnico->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tecnico_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-calendar me-2"></i> Fechas
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="fecha_ingreso" class="form-label">
                                Fecha de Ingreso <span class="text-danger">*</span>
                            </label>
                            <input
                                type="date"
                                name="fecha_ingreso"
                                id="fecha_ingreso"
                                class="form-control @error('fecha_ingreso') is-invalid @enderror"
                                value="{{ old('fecha_ingreso', $orden->fecha_ingreso->format('Y-m-d')) }}"
                                required
                            >
                            @error('fecha_ingreso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fecha_estimada_entrega" class="form-label">
                                Fecha Estimada de Entrega
                            </label>
                            <input
                                type="date"
                                name="fecha_estimada_entrega"
                                id="fecha_estimada_entrega"
                                class="form-control @error('fecha_estimada_entrega') is-invalid @enderror"
                                value="{{ old('fecha_estimada_entrega', $orden->fecha_estimada_entrega?->format('Y-m-d')) }}"
                            >
                            @error('fecha_estimada_entrega')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="fecha_entrega" class="form-label">
                                Fecha de Entrega
                            </label>
                            <input
                                type="date"
                                name="fecha_entrega"
                                id="fecha_entrega"
                                class="form-control @error('fecha_entrega') is-invalid @enderror"
                                value="{{ old('fecha_entrega', $orden->fecha_entrega?->format('Y-m-d')) }}"
                            >
                            @error('fecha_entrega')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Presupuesto -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-currency-dollar me-2"></i> Presupuesto
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="presupuesto" class="form-label">
                                Monto del Presupuesto
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input
                                    type="number"
                                    name="presupuesto"
                                    id="presupuesto"
                                    class="form-control @error('presupuesto') is-invalid @enderror"
                                    value="{{ old('presupuesto', $orden->presupuesto) }}"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                >
                                @error('presupuesto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input
                                    type="checkbox"
                                    name="presupuesto_aprobado"
                                    id="presupuesto_aprobado"
                                    class="form-check-input"
                                    value="1"
                                    {{ old('presupuesto_aprobado', $orden->presupuesto_aprobado) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="presupuesto_aprobado">
                                    Presupuesto aprobado por el cliente
                                </label>
                            </div>
                        </div>

                        <div class="mb-0" id="fecha-aprobacion-container" style="display: {{ old('presupuesto_aprobado', $orden->presupuesto_aprobado) ? 'block' : 'none' }};">
                            <label for="fecha_aprobacion_presupuesto" class="form-label">
                                Fecha de Aprobación
                            </label>
                            <input
                                type="date"
                                name="fecha_aprobacion_presupuesto"
                                id="fecha_aprobacion_presupuesto"
                                class="form-control @error('fecha_aprobacion_presupuesto') is-invalid @enderror"
                                value="{{ old('fecha_aprobacion_presupuesto', $orden->fecha_aprobacion_presupuesto?->format('Y-m-d')) }}"
                            >
                            @error('fecha_aprobacion_presupuesto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Costos -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-receipt me-2"></i> Costos
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="costo_repuestos" class="form-label">
                                Costo de Repuestos
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input
                                    type="number"
                                    name="costo_repuestos"
                                    id="costo_repuestos"
                                    class="form-control @error('costo_repuestos') is-invalid @enderror"
                                    value="{{ old('costo_repuestos', $orden->costo_repuestos ?? 0) }}"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                >
                                @error('costo_repuestos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="costo_mano_obra" class="form-label">
                                Costo de Mano de Obra
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input
                                    type="number"
                                    name="costo_mano_obra"
                                    id="costo_mano_obra"
                                    class="form-control @error('costo_mano_obra') is-invalid @enderror"
                                    value="{{ old('costo_mano_obra', $orden->costo_mano_obra ?? 0) }}"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                >
                                @error('costo_mano_obra')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info mb-0">
                            <strong>Costo Total:</strong>
                            <span id="costo-total-display" class="float-end">
                                ${{ number_format($orden->costo_total ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 .375rem .375rem;
        max-height: 220px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }
    .autocomplete-dropdown.show { display: block; }
    .autocomplete-item {
        padding: .5rem .75rem;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background .15s;
    }
    .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-item:hover, .autocomplete-item.active {
        background: var(--primary-color, #4361ee);
        color: #fff;
    }
    .autocomplete-item:hover small, .autocomplete-item.active small { color: rgba(255,255,255,.8) !important; }
    .autocomplete-item small { color: #6c757d; }
    .autocomplete-empty {
        padding: .75rem;
        text-align: center;
        color: #6c757d;
        font-size: .875rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // ===== Autocompletado de clientes =====
    (function() {
        const searchInput = document.getElementById('cliente_search');
        const hiddenInput = document.getElementById('cliente_id');
        const dropdown    = document.getElementById('cliente_results');
        let debounceTimer = null;
        let activeIndex   = -1;
        let items         = [];

        searchInput.addEventListener('input', function() {
            const q = this.value.trim();
            clearTimeout(debounceTimer);

            if (q.length < 2) {
                closeDropdown();
                if (q.length === 0) hiddenInput.value = '';
                return;
            }

            debounceTimer = setTimeout(() => fetchClientes(q), 250);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (!dropdown.classList.contains('show')) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                highlightItem();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                highlightItem();
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                selectItem(items[activeIndex]);
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                closeDropdown();
            }
        });

        function fetchClientes(q) {
            fetch('{{ route("clientes.buscar") }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    items = data;
                    activeIndex = -1;
                    renderDropdown(data);
                });
        }

        function renderDropdown(data) {
            dropdown.innerHTML = '';
            if (data.length === 0) {
                dropdown.innerHTML = '<div class="autocomplete-empty"><i class="bi bi-search me-1"></i>No se encontraron clientes</div>';
            } else {
                data.forEach((c, i) => {
                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    div.innerHTML = '<strong>' + escapeHtml(c.nombre) + '</strong><br><small>' + escapeHtml(c.telefono || '') + (c.email ? ' · ' + escapeHtml(c.email) : '') + '</small>';
                    div.addEventListener('click', () => selectItem(c));
                    div.addEventListener('mouseenter', () => { activeIndex = i; highlightItem(); });
                    dropdown.appendChild(div);
                });
            }
            dropdown.classList.add('show');
        }

        function selectItem(c) {
            searchInput.value = c.nombre + (c.telefono ? ' - ' + c.telefono : '');
            hiddenInput.value = c.id;
            closeDropdown();
            searchInput.classList.remove('is-invalid');
        }

        function highlightItem() {
            dropdown.querySelectorAll('.autocomplete-item').forEach((el, i) => {
                el.classList.toggle('active', i === activeIndex);
            });
        }

        function closeDropdown() {
            dropdown.classList.remove('show');
            activeIndex = -1;
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }
    })();

    // ===== Preview de fotos =====
    document.getElementById('fotos').addEventListener('change', function(e) {
        const preview = document.getElementById('preview-fotos');
        preview.innerHTML = '';

        if (this.files.length > 0) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-4';
                    col.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail" alt="Preview">
                    `;
                    preview.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    // Función para eliminar foto existente
    function eliminarFoto(ordenId, fotoIndex) {
        if (confirm('¿Estás seguro de eliminar esta foto?')) {
            fetch(`/ordenes/${ordenId}/eliminar-foto`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ foto_index: fotoIndex })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`foto-${fotoIndex}`).remove();
                    alert('Foto eliminada correctamente');
                } else {
                    alert('Error al eliminar la foto');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar la foto');
            });
        }
    }

    // Mostrar/ocultar fecha de aprobación según checkbox
    document.getElementById('presupuesto_aprobado').addEventListener('change', function() {
        const container = document.getElementById('fecha-aprobacion-container');
        container.style.display = this.checked ? 'block' : 'none';
        if (this.checked && !document.getElementById('fecha_aprobacion_presupuesto').value) {
            document.getElementById('fecha_aprobacion_presupuesto').value = new Date().toISOString().split('T')[0];
        }
    });

    // Calcular costo total automáticamente
    function calcularCostoTotal() {
        const repuestos = parseFloat(document.getElementById('costo_repuestos').value) || 0;
        const manoObra = parseFloat(document.getElementById('costo_mano_obra').value) || 0;
        const total = repuestos + manoObra;
        document.getElementById('costo-total-display').textContent = '$' + total.toFixed(2);
    }

    document.getElementById('costo_repuestos').addEventListener('input', calcularCostoTotal);
    document.getElementById('costo_mano_obra').addEventListener('input', calcularCostoTotal);

    // Inicializar cálculo al cargar
    calcularCostoTotal();
</script>
@endpush
@endsection
