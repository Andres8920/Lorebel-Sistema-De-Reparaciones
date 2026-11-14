@extends('layouts.app')

@section('title', 'Auditoría - Sesiones')

@section('content')
<div class="fade-in">
    <h1 class="h2 mb-1">Auditoría · Inicios y cierres de sesión</h1>
    <p class="text-muted mb-4">Registro de login, logout e intentos fallidos</p>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Logins hoy</div><div class="fs-3 fw-bold text-success">{{ $resumen['login'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Logouts hoy</div><div class="fs-3 fw-bold text-primary">{{ $resumen['logout'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Intentos fallidos hoy</div><div class="fs-3 fw-bold text-danger">{{ $resumen['login_fallido'] }}</div></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Evento</label>
                    <select name="evento" class="form-select">
                        <option value="">Todos</option>
                        @foreach(['login' => 'Login', 'logout' => 'Logout', 'login_fallido' => 'Login fallido'] as $k => $t)
                            <option value="{{ $k }}" @selected(request('evento') === $k)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Usuario</label>
                    <select name="user_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}" @selected((string) request('user_id') === (string) $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label small">Desde</label><input type="date" name="desde" value="{{ request('desde') }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label small">Hasta</label><input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filtrar</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Fecha y hora</th><th>Evento</th><th>Usuario / email</th><th>IP</th><th>Navegador</th></tr></thead>
                <tbody>
                    @forelse($registros as $r)
                        <tr>
                            <td class="text-nowrap">{{ $r->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @php $c = ['login' => ['success', 'Login'], 'logout' => ['primary', 'Logout'], 'login_fallido' => ['danger', 'Login fallido']][$r->evento]; @endphp
                                <span class="badge bg-{{ $c[0] }}">{{ $c[1] }}</span>
                            </td>
                            <td>{{ $r->usuario?->name ?? '—' }} <span class="text-muted small">{{ $r->email }}</span></td>
                            <td class="small">{{ $r->ip }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($r->user_agent, 50) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $registros->links() }}
</div>
@endsection
