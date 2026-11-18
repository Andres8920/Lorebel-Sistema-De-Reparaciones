# Lorebel – Sistema de Gestión de Reparaciones

Sistema web para talleres de reparación de dispositivos electrónicos. Administra clientes, técnicos y órdenes de reparación (estado, presupuesto, fotos y comprobante) y, desde la versión 2.0, el **cobro de las órdenes** con seis medios de pago, inicio de sesión con roles, auditoría y reportes.

Desarrollado con Laravel 12, Blade y Bootstrap 5. Base de datos MySQL (las pruebas usan SQLite en memoria).

Documentación completa del proyecto: [`LOREBEL.pdf`](LOREBEL.pdf). Cambios de la iteración 2: [`CAMBIOS_ITERACION_2.md`](CAMBIOS_ITERACION_2.md).

---

## Requisitos

- **PHP 8.4** con las extensiones `pdo_mysql`, `pdo_sqlite`, `mbstring` y `xml`
- Composer
- MySQL 8
- Node.js (para compilar assets con Vite)

> El `composer.lock` actual incluye componentes de Symfony 8, que exigen PHP 8.4. Para instalar con PHP 8.2 u 8.3, regenerar el lock fijando la plataforma:
> `composer config platform.php 8.2.0 && composer update`

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/Andres8920/Lorebel-Sistema-De-Reparaciones.git
cd Lorebel-Sistema-De-Reparaciones

# 2. Instalar dependencias
composer install
npm install

# 3. Copiar el archivo de entorno y generar la clave
cp .env.example .env
php artisan key:generate

# 4. Configurar la base de datos en .env
#    DB_CONNECTION=mysql
#    DB_DATABASE=lorebel
#    DB_USERNAME=root
#    DB_PASSWORD=

# 5. Crear las tablas y cargar los datos de demostración
php artisan migrate:fresh --seed
php artisan storage:link
```

## Iniciar el proyecto

```bash
php artisan serve   # Terminal 1 - servidor PHP
npm run dev         # Terminal 2 - assets (CSS/JS)
```

Abrir <http://localhost:8000>.

Usuarios de demostración (contraseña `Lorebel2026!`):

| Usuario | Rol |
|---|---|
| admin@lorebel.com | Administrador |
| caja@lorebel.com | Recepción / Caja |
| tecnico@lorebel.com | Técnico |

---

## Módulos

### Clientes
- Alta, edición, detalle y baja lógica.
- Nombre, teléfono, WhatsApp, email, DNI/CUIT y notas internas.
- Búsqueda con autocompletado en el formulario de órdenes.

### Técnicos
- Alta, edición, detalle y baja lógica; estado activo/inactivo.
- Solo los técnicos activos se asignan a órdenes.

### Órdenes de reparación
- Datos del equipo (dispositivo, marca, modelo, IMEI/serie), problema, diagnóstico y trabajo realizado.
- Estados: ingresado, en reparación, esperando repuesto, listo, entregado, cancelado.
- Presupuesto con aprobación del cliente, fotos del equipo y comprobante PDF.
- Filtros por estado, técnico y búsqueda general.
- No se puede entregar un equipo con saldo pendiente (RN09).

### Cobros (iteración 2)
- Efectivo, transferencia, Mercado Pago (Checkout Pro), MODO, tarjeta de débito y tarjeta de crédito en 1, 3 o 6 cuotas con recargo.
- Cobros totales o parciales y seña de hasta el 50 % mientras el equipo no está listo.
- Confirmación manual de transferencias, anulación de cobros pendientes y reintegro de cobros aprobados.
- Notificaciones (webhooks) de Mercado Pago y MODO. Sin credenciales, ambas pasarelas funcionan en **modo simulación**.

### Seguridad y auditoría
- Inicio de sesión obligatorio con tres roles: administrador, recepción/caja y técnico.
- Trazabilidad de altas, modificaciones y bajas de órdenes, pagos y clientes (valor anterior y nuevo, usuario, IP).
- Registro de inicios, cierres e intentos fallidos de sesión.

### Reportes
- Recaudación por medio de pago (indicadores y gráficos).
- Productividad de técnicos.

---

## Pruebas

```bash
php artisan test
```

Pruebas de unidad (ruta básica sobre `PagoService::validarPago()`) y funcionales (cobros por medio de pago, reglas de negocio, anulación y reintegro, webhooks, seguridad por rol, auditoría y reportes). La API de Mercado Pago se simula con `Http::fake()`.

---

## Estructura del proyecto

```
app/
├── Http/
│   ├── Controllers/     # Orden, Cliente, Tecnico, Pago, Webhook, Reporte, Auditoria, Auth/Login
│   ├── Middleware/      # VerificarRol
│   └── Requests/        # Validación de formularios (OrdenRequest, PagoRequest, ...)
├── Models/              # Reparacion, Cliente, Tecnico, Pago, Auditoria, RegistroSesion, User
│   └── Concerns/        # Trait Auditable
└── Services/Pagos/      # PagoService, ResultadoPasarela y estrategias de pasarela
config/pagos.php         # Seña, recargos por cuotas, datos bancarios, credenciales
database/                # Migraciones y seeders de demostración
resources/views/         # Vistas Blade (ordenes, clientes, tecnicos, pagos, reportes, auditoria)
routes/web.php           # Rutas con middleware de autenticación y roles
tests/                   # Unit y Feature
Lorebel_diagramas_UML/   # Fuentes PlantUML y diagramas renderizados
Lorebel_capturas/        # Capturas de pantalla
```

---

## Mercado Pago y MODO

Cargar `MERCADOPAGO_ACCESS_TOKEN` (credencial `TEST-...`) y, opcionalmente, `MERCADOPAGO_WEBHOOK_SECRET` en `.env` para operar contra el sandbox real. Para recibir notificaciones en local, exponer el servidor con un túnel (por ejemplo ngrok) y apuntar `APP_URL` a esa URL pública. Las credenciales nunca se suben al repositorio.

## Tecnologías

PHP · Laravel 12 · Blade · Bootstrap 5 + Bootstrap Icons · Chart.js · MySQL · Vite · PHPUnit
