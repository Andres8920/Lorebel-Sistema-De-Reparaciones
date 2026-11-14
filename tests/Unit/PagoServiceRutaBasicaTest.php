<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Reparacion;
use App\Services\Pagos\PagoService;
use App\Services\Pagos\ReglaNegocioException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PRUEBA DE UNIDAD - CAJA BLANCA (Ruta básica) sobre PagoService::validarPago().
 *
 * Complejidad ciclomática V(G) = 8 (7 nodos predicado + 1).
 * Cada test ejecuta uno de los 8 caminos linealmente independientes:
 *
 *  C1: P1 V                       → excepción "orden cancelada"
 *  C2: P1 F, P2 V                 → excepción "presupuesto no aprobado"
 *  C3: P1 F, P2 F, P3 V           → excepción "monto <= 0"
 *  C4: … P4 V                     → excepción "supera máximo cobrable"
 *  C5: … P4 F, P5 V, P6 V         → excepción "cuotas no habilitadas"
 *  C6: … P4 F, P5 V, P6 F         → válido (crédito en cuotas)
 *  C7: … P4 F, P5 F, P7 V         → excepción "solo crédito admite cuotas"
 *  C8: … P4 F, P5 F, P7 F         → válido (contado)
 */
class PagoServiceRutaBasicaTest extends TestCase
{
    use RefreshDatabase;

    private PagoService $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        $this->servicio = app(PagoService::class);
    }

    private function orden(array $atributos = []): Reparacion
    {
        $cliente = Cliente::create([
            'nombre' => 'Cliente Prueba', 'telefono' => '341-0000000',
            'email' => uniqid() . '@test.com', 'dni_cuit' => uniqid(),
        ]);

        return Reparacion::create(array_merge([
            'cliente_id' => $cliente->id, 'dispositivo' => 'Smartphone', 'marca' => 'Samsung', 'modelo' => 'A54',
            'problema_reportado' => 'No enciende', 'estado' => 'listo', 'fecha_ingreso' => now(),
            'presupuesto' => 10000, 'presupuesto_aprobado' => true, 'costo_total' => 10000,
        ], $atributos));
    }

    private function esperarExcepcion(string $mensaje, callable $accion): void
    {
        try {
            $accion();
            $this->fail('Se esperaba ReglaNegocioException');
        } catch (ReglaNegocioException $e) {
            $this->assertStringContainsString($mensaje, $e->getMessage());
        }
    }

    public function test_c1_orden_cancelada(): void
    {
        $orden = $this->orden(['estado' => 'cancelado']);
        $this->esperarExcepcion('cancelada', fn () => $this->servicio->validarPago($orden, 'efectivo', 1000));
    }

    public function test_c2_presupuesto_no_aprobado(): void
    {
        $orden = $this->orden(['presupuesto_aprobado' => null]);
        $this->esperarExcepcion('presupuesto debe estar aprobado', fn () => $this->servicio->validarPago($orden, 'efectivo', 1000));
    }

    public function test_c3_monto_cero(): void
    {
        $orden = $this->orden();
        $this->esperarExcepcion('mayor a cero', fn () => $this->servicio->validarPago($orden, 'efectivo', 0));
    }

    public function test_c4_monto_supera_saldo(): void
    {
        $orden = $this->orden();
        $this->esperarExcepcion('supera el máximo', fn () => $this->servicio->validarPago($orden, 'efectivo', 10000.01));
    }

    public function test_c5_cuotas_no_habilitadas(): void
    {
        $orden = $this->orden();
        $this->esperarExcepcion('cuotas seleccionada no está habilitada', fn () => $this->servicio->validarPago($orden, 'tarjeta_credito', 5000, 12));
    }

    public function test_c6_credito_en_cuotas_valido(): void
    {
        $orden = $this->orden();
        $this->servicio->validarPago($orden, 'tarjeta_credito', 10000, 6);
        $this->assertTrue(true);
    }

    public function test_c7_cuotas_con_medio_que_no_es_credito(): void
    {
        $orden = $this->orden();
        $this->esperarExcepcion('Solo la tarjeta de crédito', fn () => $this->servicio->validarPago($orden, 'mercado_pago', 5000, 3));
    }

    public function test_c8_pago_contado_valido(): void
    {
        $orden = $this->orden();
        $this->servicio->validarPago($orden, 'efectivo', 10000);
        $this->assertTrue(true);
    }

    // ---- Pruebas complementarias de montoMaximoCobrable (RN04 / RN05) ----

    public function test_sena_maxima_del_50_por_ciento_si_el_equipo_no_esta_listo(): void
    {
        $orden = $this->orden(['estado' => 'en_reparacion']);
        $this->assertSame(5000.0, $this->servicio->montoMaximoCobrable($orden));
    }

    public function test_los_pagos_pendientes_reducen_el_maximo_cobrable(): void
    {
        $orden = $this->orden();
        Pago::create(['reparacion_id' => $orden->id, 'medio_pago' => 'transferencia', 'estado' => 'pendiente', 'monto' => 4000, 'monto_total' => 4000]);
        Pago::create(['reparacion_id' => $orden->id, 'medio_pago' => 'efectivo', 'estado' => 'aprobado', 'monto' => 1000, 'monto_total' => 1000]);

        $this->assertSame(5000.0, $this->servicio->montoMaximoCobrable($orden->fresh()));
    }

    public function test_calculo_de_recargo_por_cuotas(): void
    {
        $this->assertSame(0.0, $this->servicio->calcularRecargo('tarjeta_credito', 10000, 1));
        $this->assertSame(1000.0, $this->servicio->calcularRecargo('tarjeta_credito', 10000, 3));
        $this->assertSame(2000.0, $this->servicio->calcularRecargo('tarjeta_credito', 10000, 6));
        $this->assertSame(0.0, $this->servicio->calcularRecargo('efectivo', 10000, 1));
    }
}
