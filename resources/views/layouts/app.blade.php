<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lorebel') - Sistema de Gestión</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
        }

        /* Navbar personalizada */
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            border-bottom: 1px solid var(--border-color);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }

        .nav-link {
            color: var(--secondary-color) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: color 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-color) !important;
        }

        /* Botones personalizados */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);
        }

        .btn-success {
            background-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            background-color: transparent;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Botones de acción: cada botón es una caja separada con borde y pequeño espacio entre ellos */
        .btn-group.action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem; /* espacio entre botones */
            border: none;
            padding: 0;
        }

        .btn-group.action .action-btn {
            border-radius: 8px !important;
            padding: 0.45rem 0.9rem !important;
            min-width: 42px;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background-color: transparent !important;
            border: 2px solid var(--border-color) !important; /* borde sutil por defecto */
        }

        /* Borde y color para acciones primarias/primarias outline */
        .btn-group.action .action-btn.btn-outline-primary {
            border-color: var(--primary-color) !important;
        }

        .btn-group.action .action-btn.btn-outline-danger {
            border-color: var(--danger-color) !important;
        }

        .btn-group.action .action-btn i {
            color: currentColor;
            font-size: 1rem;
        }

        /* Estilo específico para el botón Eliminar integrado: hover/focus en rojo */
        .btn.action-delete {
            /* icono por defecto en rojo (ya aplicado en markup) */
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn.action-delete i {
            color: var(--danger-color);
        }

        .btn.action-delete:hover,
        .btn.action-delete:focus {
            background-color: var(--danger-color) !important;
            border-color: var(--danger-color) !important;
            color: #fff !important;
            box-shadow: none !important;
        }

        .btn.action-delete:hover i,
        .btn.action-delete:focus i {
            color: #fff !important;
        }

        /* Variante conectada: los botones aparecen dentro de un único contenedor redondeado */
        .btn-group.action.connected {
            border-radius: 10px;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            border: 2px solid var(--primary-color);
            gap: 0; /* sin espacio interno entre botones */
            padding: 0;
            background-color: transparent;
        }

        .btn-group.action.connected .action-btn {
            border-radius: 0 !important;
            padding: 0.45rem 0.9rem !important;
            min-width: 42px;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background-color: transparent !important;
            border: none !important;
            color: var(--primary-color);
        }

        .btn-group.action.connected .action-btn + .action-btn {
            border-left: 1px solid rgba(37, 99, 235, 0.12); /* separación sutil interior */
        }

        .btn-group.action.connected .action-btn i {
            color: var(--primary-color);
        }

        .btn-group.action.connected .action-btn.btn-outline-danger i {
            color: var(--danger-color);
        }

        /* Cards modernas */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            transition: box-shadow 0.2s;
        }

        .card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem;
            font-weight: 600;
            font-size: 1.125rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Tablas */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background-color: var(--light-color);
            color: var(--secondary-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--border-color);
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }

        .table tbody tr:hover {
            background-color: #f1f5f9;
        }

        /* Formularios */
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.625rem 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Badges */
        .badge {
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        /* Alertas */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.25rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Paginación */
        .pagination {
            margin-top: 1.5rem;
        }

        .page-link {
            color: var(--primary-color);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            margin: 0 0.25rem;
            padding: 0.5rem 0.875rem;
        }

        .page-link:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Contenedor principal */
        .main-container {
            padding-top: 2rem;
            padding-bottom: 2rem;
            min-height: calc(100vh - 70px);
        }

        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('ordenes.index') }}">
                <i class="bi bi-gear-fill"></i> Lorebel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('ordenes.*') ? 'active' : '' }}" href="{{ route('ordenes.index') }}">
                            <i class="bi bi-tools"></i> Órdenes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                            <i class="bi bi-people-fill"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tecnicos.*') ? 'active' : '' }}" href="{{ route('tecnicos.index') }}">
                            <i class="bi bi-person-gear"></i> Técnicos
                        </a>
                    </li>
                    @auth
                        @if(auth()->user()->tieneRol('administrador', 'recepcion'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pagos.*') ? 'active' : '' }}" href="{{ route('pagos.index') }}">
                                    <i class="bi bi-cash-stack"></i> Cobros
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}">
                                    <i class="bi bi-bar-chart-line"></i> Reportes
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->esAdministrador())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('auditoria.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-shield-check"></i> Auditoría
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('auditoria.index') }}"><i class="bi bi-clock-history me-2"></i>Trazabilidad</a></li>
                                    <li><a class="dropdown-item" href="{{ route('auditoria.sesiones') }}"><i class="bi bi-door-open me-2"></i>Inicios y cierres de sesión</a></li>
                                </ul>
                            </li>
                        @endif
                        <li class="nav-item dropdown ms-lg-2">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->rol_texto }}</span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container main-container">
        <!-- Alertas de sesión -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5" style="background-color: white; border-top: 1px solid var(--border-color);">
        <div class="container">
            <p class="mb-0 text-muted">
                <small>&copy; {{ date('Y') }} Lorebel - Sistema de Gestión de Reparaciones</small>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
