## Lorebel — Documentación para entrega y evaluación

Este documento está pensado para que una persona presente la aplicación ante un comité evaluador. Contiene: resumen del proyecto, requisitos, pasos de instalación y puesta en marcha en Windows (PowerShell), cómo probar las funcionalidades clave, checklist de evaluación y troubleshooting.

---

## 1) Resumen del proyecto

- Nombre: Lorebel — Sistema de gestión para taller de reparaciones.
- Propósito: Permitir administrar clientes, órdenes de reparación, presupuestos, técnicos y generar comprobantes (PDF) de cada orden.
- Stack principal:
  - Backend principal: Laravel (PHP) — Blade templates (vistas), controladores y migraciones.
  - Node backend (servicios complementarios): carpeta `backend/` (API/servicios auxiliares si aplica).
  - Frontend assets: mezcla de Blade + recursos compilados con Vite/NPM (carpeta `frontend/`).
  - Base de datos: MySQL (u otra compatible configurada en `.env`).
- Requisitos de evaluación: la aplicación debe arrancar, permitir crear/consultar órdenes, generar PDF de comprobante y mostrar el flujo básico (crear cliente → crear orden → generar PDF → eliminar orden).

---

## 2) Entregables en el workspace

Los cambios realizados durante esta sesión están en el workspace y NO incluyen commits ni pushes. Archivos importantes:

- `resources/views/ordenes/index.blade.php` — Lista de órdenes.
- `resources/views/ordenes/show.blade.php` — Detalle de orden (encabezado, botones de acción). Protecciones añadidas para relaciones nulas.
- `resources/views/ordenes/pdf.blade.php` — Plantilla del comprobante PDF (protecciones añadidas para cliente/tecnico).
- `resources/views/clientes/index.blade.php` — Lista de clientes (estética de botones ajustada previamente).
- `resources/views/layouts/app.blade.php` — Estilos globales y utilidades (se añadieron reglas CSS para action buttons anteriormente).
- `docs/PRESENTACION.md` — (este archivo) documentación de entrega.

> Nota: No se han hecho commits ni push en el repositorio; toda la edición quedó en los archivos locales del workspace.

---

## 3) Requisitos del sistema (entorno de evaluación)

- Sistema operativo: Windows (PowerShell sobre Windows 10/11 funciona). También se puede ejecutar en Linux/macOS con equivalentes.
- PHP: 8.1+ (se usó PHP 8.4.8 en desarrollo; Laravel 12 compatible con 8.1+). Verifica `php -v`.
- Composer (para dependencias PHP).
- Node.js + npm (para activos frontend y backend opcional).
- Base de datos: MySQL o MariaDB (o SQLite para pruebas rápidas, ver nota más abajo).

Recomendación mínima:
- PHP 8.1+ (8.4 recomendado)
- Composer 2.x
- Node 16+ / npm 8+

---

## 4) Preparación rápida (Windows PowerShell)

1) Abrir PowerShell en la raíz del proyecto:

```powershell
# Instalar dependencias PHP
composer install --no-dev --prefer-dist

# Instalar dependencias frontend (si usas assets modernos)
cd frontend
npm install
cd ..

# (Opcional) instalar dependencias del backend de Node
cd backend
npm install
cd ..
```

2) Configurar `.env` (copia de `.env.example` o crear uno nuevo):

- Copiar archivo de ejemplo y configurar DB:

```powershell
copy .env.example .env
# Edita .env con tu editor: coloca DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
```

- Generar la clave de la aplicación:

```powershell
php artisan key:generate
```

3) Crear la base de datos y ejecutar migraciones + seeders:

```powershell
# Si usas MySQL crea la base de datos desde tu cliente (phpMyAdmin o MySQL shell)
# Luego:
php artisan migrate --seed
```

Si prefieres una opción más rápida sin configurar MySQL, puedes usar SQLite:

```powershell
# crear archivo sqlite
New-Item -Path database\database.sqlite -ItemType File
# en .env: DB_CONNECTION=sqlite, comentar DB_HOST/DB_* y poner DB_DATABASE=database/database.sqlite
php artisan migrate --seed
```

4) Vincular storage (si necesitas ver imágenes subidas):

```powershell
php artisan storage:link
```

5) Compilar assets (opcional para front dinámico):

```powershell
cd frontend
npm run build
# o en modo dev:
npm run dev
cd ..
```

6) Ejecutar servidor de desarrollo Laravel:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
# Abre en el navegador: http://127.0.0.1:8000
```

7) Ejecutar servidor Node (si es necesario)

```powershell
cd backend
node server.js
```

---

## 5) Credenciales / Usuarios de evaluación

- Si los seeders incluyen un usuario admin, indica al evaluador las credenciales. Si no, crea un usuario con tinker:

```powershell
php artisan tinker
>>> \App\Models\User::create(['name' => 'Evaluador', 'email' => 'evaluador@example.com', 'password' => bcrypt('secret123')]);
```

- Iniciar sesión en `/login` con esas credenciales.

---

## 6) Flujo de demostración (guion paso a paso)

Sigue este guion para la presentación (cada paso incluye el resultado esperado):

1) Iniciar la app y abrir la URL base.
   - Esperado: página principal con navegación.
2) Iniciar sesión (si el sistema requiere auth).
   - Esperado: acceso al panel principal.
3) Ir a Clientes → Crear Cliente Nuevo
   - Acciones: Click en "Nuevo Cliente", completar nombre/teléfono/email, guardar.
   - Esperado: el cliente aparece en la lista con opciones editar/eliminar.
4) Ir a Órdenes → Crear Nueva Orden
   - Acciones: Click en "Nueva Orden", seleccionar cliente recién creado, completar dispositivo/marca/modelo/problema reportado y guardar.
   - Esperado: la orden aparece en la lista, con número, cliente, dispositivo y acciones.
5) Ver detalles de la orden → Generar Comprobante PDF
   - Acciones: Click en el botón "Comprobante PDF" (en el encabezado o en la fila), se abrirá una vista estilo comprobante con opción para imprimir.
   - Esperado: PDF/plantilla del comprobante con datos del cliente, equipo, diagnóstico y firma.
6) Editar la orden → Cambiar estado / asignar técnico
   - Acciones: Editar campos y actualizar.
   - Esperado: cambios reflejados en la vista de detalle y listado.
7) Eliminar una orden (modal de confirmación)
   - Acciones: Click en "Eliminar" y confirmar.
   - Esperado: orden eliminada; si la orden no tenía cliente, UI maneja el caso sin errores (protección aplicada).

Notas importantes para la demo:
- Si hay una orden sin cliente asignado, las vistas protegen esos accesos y muestran placeholders (p. ej. "Sin cliente").
- La generación de PDF abre una plantilla limpia; el botón "Imprimir" está disponible.

---

## 7) Checklist de verificación para evaluadores (rápido)

- [ ] La aplicación arranca con `php artisan serve`.
- [ ] Se puede iniciar sesión (o se indica cómo crear un usuario).
- [ ] Crear/editar/eliminar clientes funciona.
- [ ] Crear/editar/eliminar órdenes funciona.
- [ ] Generar comprobante PDF abre correctamente y puede imprimirse.
- [ ] No se producen errores tipo "Attempt to read property 'nombre' on null" al abrir órdenes sin cliente.
- [ ] Las tablas y paginación en `ordenes` funcionan y muestran badges de estado.

---

## 8) Endpoints y rutas clave (resumen)

- Vistas principales (Laravel routes):
  - `/ordenes` — Lista de órdenes
  - `/ordenes/create` — Formulario nueva orden
  - `/ordenes/{orden}` — Detalle de orden
  - `/ordenes/{orden}/edit` — Editar orden
  - `/ordenes/{orden}/pdf` — Generar comprobante (vista PDF)
  - `/clientes` — Lista de clientes
  - `/clientes/{cliente}` — Detalle cliente

- API/Backend node (si aplica): revisar `backend/server.js` para rutas expuestas.

---

## 8.1) Gestión de Técnicos

Este apartado describe cómo administrar los técnicos dentro de la aplicación: crear, listar, editar, eliminar y asignarlos a órdenes.
   - Rutas y vistas principales (Laravel):
      - `/tecnicos` — Lista de técnicos (CRUD básico).
      - `/tecnicos/create` — Formulario para crear técnico.
      - `/tecnicos/{tecnico}` — Detalle de técnico.
      - `/tecnicos/{tecnico}/edit` — Editar técnico.

   - Campos típicos en el modelo `Tecnico`:
      - `nombre_completo` (string) — Nombre visible en listas y asignaciones.
      - `telefono` (string) — Teléfono de contacto.
      - `email` (string) — Correo electrónico.
      - `especialidad` (string, opcional) — P. ej. "Celulares", "Notebooks", "Electrónica".
      - `activo` (boolean, opcional) — Indica si el técnico está disponible para asignaciones.

   - Flujo y notas para la demo:
      1) Ir a `Técnicos` en el menú y crear un técnico nuevo con nombre, teléfono y especialidad.
      2) Verificar que el técnico aparece en la lista con opciones de editar y eliminar.
      3) Editar un técnico para cambiar la especialidad o datos de contacto.
      4) Desde la vista de creación/edición de una `Orden`, seleccionar un técnico del listado para asignarlo a la orden.
      5) En la vista de `Orden` (detalle) se mostrará el técnico asignado; si no hay técnico, se mostrará "Sin asignar".

   - Asignación a órdenes:
      - El formulario de creación/edición de órdenes incluye normalmente un campo `tecnico_id` que referencia al técnico.
      - Asegúrate de que el select de técnicos filtra por técnicos `activo = true` (o muestra una opción "Sin asignar").

   - Seeders y tinker (crear técnicos rápidamente):

   ```powershell
   # Crear técnico con tinker
   php artisan tinker
   >>> \App\Models\Tecnico::create(['nombre_completo' => 'Juan Perez', 'telefono' => '011-1234-5678', 'email' => 'juan@example.com', 'especialidad' => 'Celulares']);
   ```

   - Validaciones recomendadas (form request):
      - `nombre_completo`: requerido, max:255
      - `telefono`: requerido, formato opcional
      - `email`: nullable|email|unique:tecnicos,email
      - `especialidad`: nullable|max:100

   - Notas sobre permisos y roles:
      - Si la app usa autorización (policies/roles), restringe creación/edición/eliminación de técnicos a usuarios con rol administración.

   - Problemas comunes:
      - Si falta la relación entre `Orden` y `Tecnico`, revisa el `belongsTo` en el modelo `Orden` y el `hasMany` en `Tecnico`.
      - Si un técnico no aparece en el select al crear una orden, verifica que no esté filtrado por `activo=false` o que haya sido marcado como eliminado lógicamente.


## 9) Pruebas automáticas (si existen)

- Ejecutar tests PHP (si hay pruebas PHPUnit configuradas):

```powershell
# Ejecutar pruebas unitarias/funcionales
vendor\bin\phpunit --configuration phpunit.xml
```

- Nota: Dependiendo de la configuración y base de datos, algunas pruebas pueden necesitar un entorno preparado (DB de testing). Si hay fallos, revisa los mensajes de error para ajustar `.env.testing`.

---

## 10) Problemas conocidos y troubleshooting rápido

- Error: "Attempt to read property 'nombre' on null" → Solución: ya se aplicaron protecciones `optional()` y condicionales `@if($orden->cliente)` en vistas de órdenes; si el error sigue, limpiar cache de vistas:

```powershell
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

- Problema: Assets CSS/JS no actualizados → ejecutar `npm run dev` o `npm run build` en `frontend/`.

- Problema: Imágenes no visibles → ejecutar `php artisan storage:link`.

---

## 11) Notas para el evaluador técnico (detalles internos)

- Código clave para revisar: controladores en `app/Http/Controllers/` (Laravel), modelos en `app/Models/`, vistas en `resources/views/`.
- Se aplicaron pequeños retoques de UI para consistencia (botones de acción, estilos en `layouts/app.blade.php`).
- Mantén en cuenta la política: no se hicieron commits ni pushes durante la edición; el autor del repo controlará el versionado.

---

## 12) Guion de demostración rápido (2 minutos)

1) Abrir la app y entrar como evaluador.
2) Mostrar la lista de órdenes (`/ordenes`) y explicar la paginación y filtros.
3) Crear una orden rápida desde `Nueva Orden` y mostrar cómo aparece en la lista.
4) Ver la orden creada y pulsar "Comprobante PDF" para abrir y mostrar el documento listo para impresión.
5) Mostrar edición de estado y asignación de técnico, y finalmente eliminar la orden mostrando el modal de confirmación.

---

## 13) Contacto / notas finales

- Si al presentar necesitan un script listado de comandos o una VM con todo preconfigurado, puedo generar un `README` alternativo con scripts de instalación (provisionamiento) o un `docker-compose.yml` básico para facilitar la evaluación.

---

Gracias: este documento busca facilitar la presentación ante un comité de evaluación. Si quieres, lo adapto a un formato PDF/Word o añado capturas de pantalla concretas (puedes subir las imágenes que quieras incluir).
