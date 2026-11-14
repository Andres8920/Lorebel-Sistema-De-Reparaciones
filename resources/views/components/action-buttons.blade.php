@php
    // Parámetros esperados:
    // $resource (string): nombre del recurso para construir el id del form (ej. 'clientes')
    // $id (int|string): id del registro
    // $showRoute, $editRoute, $deleteRoute: URLs ya resueltas o null
    $resource = $resource ?? 'item';
    $id = $id ?? '0';
    $showRoute = $showRoute ?? null;
    $editRoute = $editRoute ?? null;
    $deleteRoute = $deleteRoute ?? null;
@endphp

<div class="btn-group btn-group-sm" role="group">
    @if($showRoute)
        <a href="{{ $showRoute }}" class="btn btn-outline-primary" title="Ver detalles">
            <i class="bi bi-eye"></i>
        </a>
    @endif

    @if($editRoute)
        <a href="{{ $editRoute }}" class="btn btn-outline-primary" title="Editar">
            <i class="bi bi-pencil"></i>
        </a>
    @endif

    @if($deleteRoute)
        <button
            type="button"
            class="btn btn-outline-primary action-delete"
            onclick="confirmDeletion('{{ $resource }}', {{ $id }})"
            title="Eliminar"
        >
            <i class="bi bi-trash text-danger"></i>
        </button>
    @endif
</div>

@if($deleteRoute)
    <form id="delete-{{ $resource }}-{{ $id }}" action="{{ $deleteRoute }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    @push('scripts')
    <script>
        if (typeof confirmDeletion !== 'function') {
            function confirmDeletion(resource, id) {
                var msg = '¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.';
                if (confirm(msg)) {
                    var form = document.getElementById('delete-' + resource + '-' + id);
                    if (form) form.submit();
                }
            }
        }
    </script>
    @endpush
@endif
