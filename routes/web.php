<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TecnicoController;
use App\Http\Controllers\WebhookController;

// ---------------------------------------------------------------------
// Autenticación
// ---------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});
Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ---------------------------------------------------------------------
// Webhooks de pasarelas de pago (públicos, sin CSRF - ver bootstrap/app.php)
// ---------------------------------------------------------------------
Route::post('webhooks/mercadopago', [WebhookController::class, 'mercadoPago'])->name('webhooks.mercadopago');
Route::post('webhooks/modo', [WebhookController::class, 'modo'])->name('webhooks.modo');

// ---------------------------------------------------------------------
// Aplicación (requiere sesión iniciada)
// ---------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Ruta principal redirige a órdenes
    Route::get('/', function () {
        return redirect()->route('ordenes.index');
    });

    // Búsqueda de clientes vía AJAX (ANTES del resource para evitar conflicto con {cliente})
    Route::get('clientes/buscar', [ClienteController::class, 'buscar'])->name('clientes.buscar');

    // Rutas del módulo de Clientes (CRUD completo)
    Route::resource('clientes', ClienteController::class);

    // Rutas adicionales para órdenes (ANTES del resource para evitar conflictos)
    Route::delete('ordenes/{orden}/foto', [OrdenController::class, 'eliminarFoto'])->name('ordenes.eliminar-foto');
    Route::get('ordenes/{orden}/pdf', [OrdenController::class, 'generarPDF'])->name('ordenes.pdf');

    // Rutas del módulo de Órdenes de Reparación (con parámetro personalizado 'orden')
    Route::resource('ordenes', OrdenController::class)->parameters(['ordenes' => 'orden']);

    // Rutas del módulo de Técnicos
    Route::resource('tecnicos', TecnicoController::class);

    // -----------------------------------------------------------------
    // Iteración 2 - CORE: Cobro de órdenes de reparación
    // -----------------------------------------------------------------
    Route::middleware('rol:administrador,recepcion')->group(function () {
        Route::get('pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::get('ordenes/{orden}/cobrar', [PagoController::class, 'create'])->name('pagos.create');
        Route::post('ordenes/{orden}/pagos', [PagoController::class, 'store'])->name('pagos.store');
        Route::post('pagos/{pago}/confirmar', [PagoController::class, 'confirmar'])->name('pagos.confirmar');
        Route::post('pagos/{pago}/anular', [PagoController::class, 'anular'])->name('pagos.anular');
        Route::get('pagos/{pago}/simulador', [PagoController::class, 'simulador'])->name('pagos.simulador');
        Route::post('pagos/{pago}/simulador', [PagoController::class, 'procesarSimulacion'])->name('pagos.simulador.procesar');
    });
    Route::get('pagos/{pago}', [PagoController::class, 'show'])->name('pagos.show');
    Route::get('pagos/{pago}/retorno', [PagoController::class, 'retorno'])->name('pagos.retorno');
    Route::post('pagos/{pago}/reintegrar', [PagoController::class, 'reintegrar'])
        ->middleware('rol:administrador')->name('pagos.reintegrar');

    // -----------------------------------------------------------------
    // Reportes / indicadores
    // -----------------------------------------------------------------
    Route::middleware('rol:administrador,recepcion')->prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('recaudacion', [ReporteController::class, 'recaudacion'])->name('recaudacion');
        Route::get('tecnicos', [ReporteController::class, 'tecnicos'])->name('tecnicos');
    });

    // -----------------------------------------------------------------
    // Auditoría (solo administrador)
    // -----------------------------------------------------------------
    Route::middleware('rol:administrador')->prefix('auditoria')->name('auditoria.')->group(function () {
        Route::get('/', [AuditoriaController::class, 'index'])->name('index');
        Route::get('sesiones', [AuditoriaController::class, 'sesiones'])->name('sesiones');
        Route::get('ordenes/{id}', [AuditoriaController::class, 'orden'])->whereNumber('id')->name('orden');
    });
});
