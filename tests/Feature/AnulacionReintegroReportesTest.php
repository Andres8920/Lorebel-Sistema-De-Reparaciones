<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Reparacion;
use App\Models\Tecnico;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de validación de la regla RN08 (anulación y reintegro de cobros)
 * y de los reportes de recaudación y de productividad de técnicos.
 */
class AnulacionReintegroReportesTest extends TestCase
{
    use RefreshDatabase;

    private User $caja;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        config(['pagos.mercado_pago.access_token' => null, 'pagos.modo.client_id' => null]);
        $this->caja = User::factory()->create(['rol' => 'recepcion']);
        $this->admin = User::factory()->create(['rol' => 'administrador']);
    }

    private function orden(array $atributos = []): Reparacion
    {
        $cliente = Cliente::create([
            'nombre' => 'Luis Gómez', 'telefono' => '341-2222222', 'email' => uniqid() . '@test.com', 'dni_cuit' => uniqid(),
        ]);

        return Reparacion::create(array_merge([
            'cliente_id' => $cliente->id, 'dispositivo' => 'Smartphone', 'marca' => 'Samsung', 'modelo' => 'A54',
            'problema_reportado' => 'Pantalla', 'estado' => 'listo', 'fecha_ingreso' => now(),
            'presupuesto' => 20000, 'presupuesto_aprobado' => true, 'costo_total' => 20000,
        ], $atributos));
    }

    private function cobrar(Reparacion $orden, string $medio, string $monto): Pago
    {
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => $medio, 'monto' => $monto]);

        return Pago::where('reparacion_id', $orden->id)->latest('id')->first();
    }

    // ---------------- RN08: anulación ----------------

    public function test_anular_un_pago_pendiente_libera_el_monto_reservado(): void
    {
        $orden = $this->orden();
        $pago = $this->cobrar($orden, 'transferencia', '20000');
        $this->assertSame('pendiente', $pago->estado);

        $this->actingAs($this->caja)->post(route('pagos.anular', $pago), ['motivo' => 'El cliente no transfirió'])
            ->assertSessionHas('success');

        $this->assertSame('anulado', $pago->fresh()->estado);
        $this->assertSame(20000.0, $orden->fresh()->saldo);
        $this->assertSame(20000.0, app(\App\Services\Pagos\PagoService::class)->montoMaximoCobrable($orden->fresh()));
    }

    public function test_no_se_puede_anular_un_pago_aprobado(): void
    {
        $orden = $this->orden();
        $pago = $this->cobrar($orden, 'efectivo', '20000');
        $this->assertSame('aprobado', $pago->estado);

        $this->actingAs($this->caja)->post(route('pagos.anular', $pago), ['motivo' => 'Error'])
            ->assertSessionHas('error');

        $this->assertSame('aprobado', $pago->fresh()->estado);
        $this->assertSame(0.0, $orden->fresh()->saldo);
    }

    public function test_anular_exige_motivo(): void
    {
        $pago = $this->cobrar($this->orden(), 'transferencia', '20000');

        $this->actingAs($this->caja)->post(route('pagos.anular', $pago), ['motivo' => ''])
            ->assertSessionHasErrors('motivo');

        $this->assertSame('pendiente', $pago->fresh()->estado);
    }

    // ---------------- RN08: reintegro ----------------

    public function test_el_administrador_reintegra_un_pago_aprobado_y_el_saldo_aumenta(): void
    {
        $orden = $this->orden();
        $pago = $this->cobrar($orden, 'efectivo', '20000');
        $this->assertSame(0.0, $orden->fresh()->saldo);

        $this->actingAs($this->admin)->post(route('pagos.reintegrar', $pago), ['motivo' => 'Garantía'])
            ->assertSessionHas('success');

        $this->assertSame('reintegrado', $pago->fresh()->estado);
        $this->assertSame(20000.0, $orden->fresh()->saldo);
        $this->assertSame('impaga', $orden->fresh()->estado_cobro);
    }

    public function test_recepcion_no_puede_reintegrar(): void
    {
        $pago = $this->cobrar($this->orden(), 'efectivo', '20000');

        $this->actingAs($this->caja)->post(route('pagos.reintegrar', $pago), ['motivo' => 'Garantía'])
            ->assertForbidden();

        $this->assertSame('aprobado', $pago->fresh()->estado);
    }

    public function test_no_se_puede_reintegrar_un_pago_pendiente(): void
    {
        $pago = $this->cobrar($this->orden(), 'transferencia', '20000');

        $this->actingAs($this->admin)->post(route('pagos.reintegrar', $pago), ['motivo' => 'Garantía'])
            ->assertSessionHas('error');

        $this->assertSame('pendiente', $pago->fresh()->estado);
    }

    // ---------------- Reportes ----------------

    public function test_reporte_de_recaudacion_calcula_los_indicadores(): void
    {
        $ordenA = $this->orden();
        $ordenB = $this->orden();

        $this->cobrar($ordenA, 'efectivo', '10000');                       // aprobado en el acto
        $transferencia = $this->cobrar($ordenB, 'transferencia', '20000'); // pendiente hasta confirmar
        $this->actingAs($this->caja)->post(route('pagos.confirmar', $transferencia), ['numero_operacion' => 'OP-1']);

        $respuesta = $this->actingAs($this->caja)->get(route('reportes.recaudacion'));
        $respuesta->assertOk();

        $indicadores = $respuesta->viewData('indicadores');
        $this->assertEquals(30000.0, $indicadores['total_cobrado']);
        $this->assertSame(2, $indicadores['cantidad_cobros']);
        $this->assertEquals(15000.0, $indicadores['ticket_promedio']);
        $this->assertSame('Transferencia bancaria', $indicadores['medio_principal']);
        $this->assertEqualsWithDelta(66.67, $indicadores['porcentaje_digital'], 0.01);
        $this->assertEquals(10000.0, $indicadores['por_cobrar']);  // saldo de la orden A (lista)
    }

    public function test_reporte_de_recaudacion_excluye_pagos_no_aprobados_y_fuera_de_periodo(): void
    {
        $this->cobrar($this->orden(), 'transferencia', '20000'); // queda pendiente

        $respuesta = $this->actingAs($this->caja)->get(route('reportes.recaudacion', [
            'desde' => now()->subDays(60)->toDateString(),
            'hasta' => now()->subDays(31)->toDateString(),
        ]));

        $indicadores = $respuesta->viewData('indicadores');
        $this->assertEquals(0.0, $indicadores['total_cobrado']);
        $this->assertEquals(20000.0, $indicadores['pendiente_acreditar']);
    }

    public function test_reporte_de_productividad_de_tecnicos(): void
    {
        $tecnico = Tecnico::create([
            'nombre' => 'Laura', 'apellido' => 'Pérez', 'email' => 'laura@test.com', 'telefono' => '341-3333333',
        ]);

        // Fechas como 'Y-m-d', igual que las envía el formulario de órdenes.
        $this->orden([
            'tecnico_id' => $tecnico->id, 'estado' => 'entregado',
            'fecha_ingreso' => now()->subDays(4)->toDateString(), 'fecha_entrega' => now()->toDateString(),
        ]);
        $this->orden(['tecnico_id' => $tecnico->id, 'estado' => 'en_reparacion', 'fecha_ingreso' => now()->subDay()->toDateString()]);

        $respuesta = $this->actingAs($this->caja)->get(route('reportes.tecnicos'));
        $respuesta->assertOk();

        $fila = $respuesta->viewData('filas')->first();
        $this->assertSame(2, $fila->asignadas);
        $this->assertSame(1, $fila->terminadas);
        $this->assertSame(1, $fila->en_curso);
        $this->assertEquals(50, $fila->efectividad);
        $this->assertEquals(4, $fila->dias_promedio);
        $this->assertEquals(20000.0, $fila->facturado);
    }

    public function test_un_tecnico_no_accede_a_los_reportes(): void
    {
        $tecnico = User::factory()->create(['rol' => 'tecnico']);

        $this->actingAs($tecnico)->get(route('reportes.recaudacion'))->assertForbidden();
        $this->actingAs($tecnico)->get(route('reportes.tecnicos'))->assertForbidden();
    }
}
