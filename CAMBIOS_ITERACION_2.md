# Lorebel – Cambios de la Iteración 2 (Trabajo Final de Ingeniería)

## Puesta en marcha
    composer install          (si no se usa la carpeta vendor incluida)
    php artisan migrate:fresh --seed     ← BORRA la base y carga datos de demo
    php artisan storage:link
    php artisan serve

Usuarios (contraseña `Lorebel2026!`): admin@lorebel.com (administrador), caja@lorebel.com (recepción/caja), tecnico@lorebel.com (técnico).

Tests: `php artisan test` (42 pruebas: 32 de la iteración 2 y 10 de anulación, reintegro y reportes en `tests/Feature/AnulacionReintegroReportesTest.php`).

Requisito de PHP: el `composer.lock` incluye Symfony 8, que exige PHP 8.4. Con PHP 8.2 u 8.3 ejecutar `composer config platform.php 8.2.0 && composer update` antes de instalar.

## Archivos nuevos
- config/pagos.php
- app/Services/Pagos/PagoService.php, ResultadoPasarela.php, ReglaNegocioException.php
- app/Services/Pagos/Pasarelas/{PasarelaPago, PasarelaFactory, EfectivoPasarela, TransferenciaPasarela, TarjetaPasarela, MercadoPagoPasarela, ModoPasarela}.php
- app/Models/{Pago, Auditoria, RegistroSesion}.php, app/Models/Concerns/Auditable.php
- app/Http/Controllers/{PagoController, WebhookController, ReporteController, AuditoriaController}.php, Auth/LoginController.php
- app/Http/Middleware/VerificarRol.php, app/Http/Requests/PagoRequest.php
- database/migrations/2026_11_10_00000{1,2,3}_*.php, database/seeders/DemoCobrosSeeder.php
- resources/views/{auth, pagos, reportes, auditoria, partials}/*, resources/views/ordenes/_pagos.blade.php
- tests/Unit/PagoServiceRutaBasicaTest.php, tests/Feature/CobroOrdenTest.php

## Archivos modificados
- routes/web.php (login, middleware auth/rol, pagos, reportes, auditoría, webhooks)
- bootstrap/app.php (alias `rol`, CSRF excluido en webhooks, redirección a login)
- app/Providers/AppServiceProvider.php (auditoría de login/logout/fallido, paginación Bootstrap)
- app/Models/{Reparacion, Cliente, User}.php (Auditable, pagos, saldo, roles)
- app/Http/Controllers/OrdenController.php (RN09: no entregar con saldo)
- app/Http/Requests/OrdenRequest.php (corrección: costo_total no se persistía)
- resources/views/layouts/app.blade.php (menú Cobros/Reportes/Auditoría/usuario)
- resources/views/ordenes/show.blade.php (tarjeta de cobros)
- database/seeders/DatabaseSeeder.php, tests/Feature/ExampleTest.php, .env.example

## Mercado Pago real
Cargar MERCADOPAGO_ACCESS_TOKEN (TEST-...) en .env. Para recibir webhooks en local
exponer el servidor con un túnel (ej. ngrok) y que APP_URL apunte a esa URL pública.
