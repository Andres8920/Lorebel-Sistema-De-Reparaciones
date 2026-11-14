<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - Lorebel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; background: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1e293b; }
        .login-card { max-width: 410px; width: 100%; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; box-shadow: 0 10px 30px rgba(15, 23, 42, .06); }
        .marca { font-weight: 700; font-size: 1.6rem; color: #2563eb; }
        .form-control { border-radius: 8px; padding: .65rem .85rem; }
        .btn-primary { background: #2563eb; border: none; border-radius: 8px; padding: .65rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center">
    <div class="login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="marca"><i class="bi bi-gear-fill"></i> Lorebel</div>
            <p class="text-muted mb-0">Gestión de reparaciones</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror" autofocus autocomplete="username">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Contraseña</label>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror" autocomplete="current-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="recordar" id="recordar">
                <label class="form-check-label" for="recordar">Mantener la sesión iniciada</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
</div>
</body>
</html>
