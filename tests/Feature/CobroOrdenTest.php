<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\RegistroSesion;
use App\Models\Reparacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * PRUEBAS DE VALIDACIÓN (caja negra) del requerimiento CORE "Registrar cobro de orden",
 * de auditoría y de seguridad por rol.
 */
class CobroOrdenTest extends TestCase
{
    use RefreshDatabase;

    private User $caja;

    protected function setUp(): void
    {
        parent::setUp();
        config(['pagos.mercado_pago.access_token' => null, 'pagos.modo.client_id' => null]);
        $this->caja = User::factory()->create(['rol' => 'recepcion']);
    }

    private function orden(array $atributos = []): Reparacion
    {
        $cliente = Cliente::create([
            'nombre' => 'Ana Ruiz', 'telefono' => '341-1111111', 'email' => uniqid() . '@test.com', 'dni_cuit' => uniqid(),
        ]);

        return Reparacion::create(array_merge([
            'cliente_id' => $cliente->id, 'dispositivo' => 'Smartphone', 'marca' => 'Motorola', 'modelo' => 'G84',
            'problema_reportado' => 'Batería', 'estado' => 'listo', 'fecha_ingreso' => now(),
            'presupuesto' => 20000, 'presupuesto_aprobado' => true, 'costo_total' => 20000,
        ], $atributos));
    }

    // ---------------- Partición de equivalencia / valores límite del monto ----------------

    public function test_valor_limite_monto_igual_al_saldo_es_valido(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja)
            ->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => '20000'])
            ->assertRedirect(route('ordenes.show', $orden));

        $this->assertSame(0.0, $orden->fresh()->saldo);
        $this->assertSame('pagada', $orden->fresh()->estado_cobro);
    }

    public function test_valor_limite_un_centavo_mas_que_el_saldo_es_rechazado(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja)
            ->from(route('pagos.create', $orden))
            ->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => '20000.01'])
            ->assertSessionHas('error');

        $this->assertSame(0, Pago::count());
    }

    public function test_valor_limite_inferior_un_centavo_es_valido_y_cero_invalido(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => '0'])
            ->assertSessionHasErrors('monto');
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => '0.01']);
        $this->assertSame('0.01', Pago::first()->monto);
    }

    public function test_valor_limite_de_la_sena_del_50_por_ciento(): void
    {
        $orden = $this->orden(['estado' => 'en_reparacion']);
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => '10000.01'])
            ->assertSessionHas('error');
        $this->assertSame(0, Pago::count());

        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => '10000']);
        $this->assertSame('aprobado', Pago::first()->estado);
    }

    public function test_particion_invalida_monto_negativo_o_vacio(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => '-5'])
            ->assertSessionHasErrors('monto');
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => ''])
            ->assertSessionHasErrors('monto');
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => 'abc'])
            ->assertSessionHasErrors('monto');
    }

    public function test_particion_invalida_medio_de_pago_inexistente(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'bitcoin', 'monto' => '100'])
            ->assertSessionHasErrors('medio_pago');
    }

    public function test_monto_con_coma_decimal_se_interpreta_correctamente(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => '1.500,50']);
        $this->assertSame('1500.50', Pago::first()->monto);
    }

    // ---------------- Medios de pago ----------------

    public function test_tarjeta_requiere_cupon_y_aplica_recargo_en_cuotas(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'tarjeta_credito', 'monto' => '10000', 'cuotas' => 3])
            ->assertSessionHasErrors('referencia');

        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'tarjeta_credito', 'monto' => '10000', 'cuotas' => 3, 'referencia' => '00123']);
        $pago = Pago::first();
        $this->assertSame('aprobado', $pago->estado);
        $this->assertSame('1000.00', $pago->recargo);
        $this->assertSame('11000.00', $pago->monto_total);
        $this->assertSame(10000.0, $orden->fresh()->saldo); // el recargo no se imputa al saldo
    }

    public function test_transferencia_queda_pendiente_y_se_acredita_al_confirmar(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'transferencia', 'monto' => '20000']);
        $pago = Pago::first();

        $this->assertSame('pendiente', $pago->estado);
        $this->assertSame(20000.0, $orden->fresh()->saldo);
        $this->assertSame(0.0, app(\App\Services\Pagos\PagoService::class)->montoMaximoCobrable($orden->fresh()));

        $this->actingAs($this->caja)->post(route('pagos.confirmar', $pago), ['numero_operacion' => 'OP-778899'])
            ->assertRedirect(route('pagos.show', $pago));

        $this->assertSame('aprobado', $pago->fresh()->estado);
        $this->assertSame(0.0, $orden->fresh()->saldo);
    }

    public function test_mercado_pago_en_simulacion_redirige_al_checkout_y_se_aprueba(): void
    {
        $orden = $this->orden();
        $respuesta = $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'mercado_pago', 'monto' => '20000']);
        $pago = Pago::first();

        $respuesta->assertRedirect(route('pagos.simulador', $pago));
        $this->actingAs($this->caja)->post(route('pagos.simulador.procesar', $pago), ['resultado' => 'aprobado'])
            ->assertRedirect(route('pagos.retorno', $pago));

        $this->assertSame('aprobado', $pago->fresh()->estado);
    }

    public function test_modo_en_simulacion_rechazado_no_imputa_el_pago(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'modo', 'monto' => '5000']);
        $pago = Pago::first();

        $this->actingAs($this->caja)->post(route('pagos.simulador.procesar', $pago), ['resultado' => 'rechazado']);

        $this->assertSame('rechazado', $pago->fresh()->estado);
        $this->assertSame(20000.0, $orden->fresh()->saldo);
    }

    public function test_mercado_pago_real_crea_preferencia_y_el_webhook_acredita_el_pago(): void
    {
        config(['pagos.mercado_pago.access_token' => 'TEST-123', 'pagos.mercado_pago.webhook_secret' => null]);
        $orden = $this->orden();

        Http::fake([
            'api.mercadopago.com/checkout/preferences' => Http::response([
                'id' => 'PREF-1', 'init_point' => 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=PREF-1',
                'sandbox_init_point' => 'https://sandbox.mercadopago.com.ar/checkout/v1/redirect?pref_id=PREF-1',
            ], 201),
        ]);

        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'mercado_pago', 'monto' => '20000'])
            ->assertRedirect('https://sandbox.mercadopago.com.ar/checkout/v1/redirect?pref_id=PREF-1');

        $pago = Pago::first();
        Http::assertSent(fn ($r) => $r['external_reference'] === $pago->codigo && $r['items'][0]['unit_price'] == 20000);

        Http::fake([
            'api.mercadopago.com/v1/payments/999' => Http::response([
                'id' => 999, 'status' => 'approved', 'status_detail' => 'accredited',
                'external_reference' => $pago->codigo, 'transaction_amount' => 20000,
            ]),
        ]);

        $this->postJson(route('webhooks.mercadopago'), ['type' => 'payment', 'data' => ['id' => '999']])->assertOk();
        $this->postJson(route('webhooks.mercadopago'), ['type' => 'payment', 'data' => ['id' => '999']])->assertOk(); // repetida

        $this->assertSame('aprobado', $pago->fresh()->estado);
        $this->assertSame('999', $pago->fresh()->referencia_externa);
        $this->assertSame(1, Pago::count()); // RN10: idempotencia
    }

    public function test_webhook_con_firma_invalida_es_rechazado(): void
    {
        config(['pagos.mercado_pago.access_token' => 'TEST-123', 'pagos.mercado_pago.webhook_secret' => 'secreto']);
        $this->postJson(route('webhooks.mercadopago'), ['type' => 'payment', 'data' => ['id' => '1']], ['x-signature' => 'ts=1,v1=falsa', 'x-request-id' => 'abc'])
            ->assertStatus(401);
    }

    // ---------------- Reglas sobre la orden ----------------

    public function test_rn09_no_se_puede_entregar_con_saldo_pendiente(): void
    {
        $orden = $this->orden();
        $datos = $orden->only(['cliente_id', 'dispositivo', 'marca', 'modelo', 'problema_reportado', 'presupuesto', 'costo_total']);
        $datos += ['estado' => 'entregado', 'fecha_ingreso' => now()->toDateString(), 'presupuesto_aprobado' => 1, 'costo_repuestos' => 10000, 'costo_mano_obra' => 10000];

        $this->actingAs($this->caja)->put(route('ordenes.update', $orden), $datos)->assertSessionHas('error');
        $this->assertSame('listo', $orden->fresh()->estado);

        $this->actingAs($this->caja)->post(route('pagos.store', $orden), ['medio_pago' => 'efectivo', 'monto' => '20000']);
        $this->actingAs($this->caja)->put(route('ordenes.update', $orden), $datos);
        $this->assertSame('entregado', $orden->fresh()->estado);
    }

    // ---------------- Seguridad y auditoría ----------------

    public function test_un_tecnico_no_puede_cobrar_ni_ver_auditoria(): void
    {
        $tecnico = User::factory()->create(['rol' => 'tecnico']);
        $orden = $this->orden();

        $this->actingAs($tecnico)->get(route('pagos.create', $orden))->assertForbidden();
        $this->actingAs($tecnico)->get(route('auditoria.index'))->assertForbidden();
        $this->actingAs($this->caja)->get(route('auditoria.index'))->assertForbidden();
        $this->actingAs($this->caja)->get(route('reportes.recaudacion'))->assertOk();
    }

    public function test_la_auditoria_registra_valores_originales_y_nuevos(): void
    {
        $orden = $this->orden();
        $this->actingAs($this->caja);
        $orden->update(['estado' => 'cancelado']);

        $registro = Auditoria::where('entidad', 'Reparacion')->where('entidad_id', $orden->id)->where('accion', 'modificacion')->first();
        $this->assertSame('listo', $registro->valores_anteriores['estado']);
        $this->assertSame('cancelado', $registro->valores_nuevos['estado']);
        $this->assertSame($this->caja->id, $registro->user_id);
    }

    public function test_login_logout_y_login_fallido_quedan_auditados(): void
    {
        $usuario = User::factory()->create(['email' => 'caja2@lorebel.com', 'password' => 'secreta123']);

        $this->post(route('login.store'), ['email' => 'caja2@lorebel.com', 'password' => 'incorrecta'])->assertSessionHasErrors('email');
        $this->post(route('login.store'), ['email' => 'caja2@lorebel.com', 'password' => 'secreta123'])->assertRedirect(route('ordenes.index'));
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertSame(['login_fallido', 'login', 'logout'], RegistroSesion::orderBy('id')->pluck('evento')->all());
        $this->assertSame($usuario->id, RegistroSesion::where('evento', 'login')->first()->user_id);
    }

    public function test_usuario_inactivo_no_puede_ingresar(): void
    {
        User::factory()->create(['email' => 'baja@lorebel.com', 'password' => 'secreta123', 'activo' => false]);
        $this->post(route('login.store'), ['email' => 'baja@lorebel.com', 'password' => 'secreta123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
