# TecnoLab - Sistema de Gestion de Reparaciones

Sistema web para la gestion de ordenes de reparacion de dispositivos electronicos. Permite administrar clientes, tecnicos y ordenes de trabajo con seguimiento de estado, presupuestos y fotos.

Desarrollado con Laravel 12, Blade y Bootstrap 5. Base de datos MySQL.

---

## Requisitos

- PHP 8.2 o superior
- Composer
- MySQL
- Node.js (para compilar assets con Vite)

---

## Instalacion

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd Lorebel

# 2. Instalar dependencias de PHP
composer install

# 3. Instalar dependencias de Node
npm install

# 4. Copiar archivo de entorno y generar clave
cp .env.example .env
php artisan key:generate

# 5. Configurar la base de datos en .env
#    DB_DATABASE=tecnolab
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Crear la base de datos y ejecutar migraciones
php artisan migrate

# 7. (Opcional) Cargar datos de prueba
php artisan db:seed
```

---

## Iniciar el proyecto

```bash
# Terminal 1 - Servidor PHP
php artisan serve

# Terminal 2 - Compilar assets (CSS/JS)
npm run dev
```

Abrir http://localhost:8000

---

## Modulos

### Clientes

- Alta, edicion, detalle y baja logica (soft delete)
- Campos: nombre, telefono, whatsapp, email, DNI/CUIT, notas internas
- Busqueda con autocompletado en el formulario de ordenes

### Tecnicos

- Alta, edicion, detalle y baja logica
- Campos: nombre, apellido, email, telefono, especialidad, estado activo/inactivo
- Solo los tecnicos activos se muestran en los formularios de ordenes

### Ordenes de reparacion

- Alta, edicion, detalle y baja
- Datos del equipo: dispositivo, marca, modelo, IMEI/serial
- Problema reportado, diagnostico tecnico, trabajo realizado
- Estados: ingresado, en reparacion, esperando repuesto, listo, entregado, cancelado
- Presupuesto con aprobacion del cliente (pendiente / aprobado)
- Carga de fotos del equipo
- Generacion de comprobante PDF
- Filtros por estado, tecnico y busqueda general

---

## Estructura del proyecto

```
Lorebel/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # OrdenController, ClienteController, TecnicoController
│   │   └── Requests/         # Validacion de formularios
│   ├── Models/               # Reparacion, Cliente, Tecnico
│   └── Providers/
├── database/
│   ├── migrations/           # Estructura de tablas
│   └── seeders/              # Datos de prueba
├── resources/
│   └── views/
│       ├── ordenes/          # Vistas de ordenes (index, create, edit, show, pdf)
│       ├── clientes/         # Vistas de clientes
│       ├── tecnicos/         # Vistas de tecnicos
│       ├── components/       # Componentes reutilizables (action-buttons)
│       └── layouts/          # Layout principal
├── routes/
│   └── web.php               # Todas las rutas de la aplicacion
├── public/                   # Archivos publicos
└── storage/                  # Fotos subidas, logs, cache
```

---

## Comandos utiles

```bash
php artisan serve                  # Iniciar servidor de desarrollo
php artisan migrate                # Ejecutar migraciones
php artisan migrate:fresh --seed   # Recrear tablas con datos de prueba
php artisan route:list             # Ver todas las rutas
php artisan cache:clear            # Limpiar cache
php artisan view:clear             # Limpiar vistas compiladas
```

---

## Tecnologias

- PHP 8.2
- Laravel 12
- Blade (motor de plantillas)
- Bootstrap 5 + Bootstrap Icons
- MySQL
- Vite (compilacion de assets)
