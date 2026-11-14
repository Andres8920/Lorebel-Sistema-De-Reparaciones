<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Iteración 2 (TFI) - Requerimiento CORE: Cobro de órdenes de reparación.
 * Un pago (cobro) pertenece a una orden. Una orden puede tener varios pagos
 * (seña + saldo, o pagos parciales con distintos medios).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->uuid('codigo')->unique();                       // referencia externa enviada a la pasarela

            $table->foreignId('reparacion_id')->constrained('reparaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // quién registró el cobro

            $table->enum('medio_pago', [
                'efectivo',
                'transferencia',
                'mercado_pago',
                'modo',
                'tarjeta_debito',
                'tarjeta_credito',
            ]);
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'anulado', 'reintegrado'])
                  ->default('pendiente');

            $table->decimal('monto', 12, 2);                        // importe que se imputa a la orden
            $table->unsignedTinyInteger('cuotas')->default(1);
            $table->decimal('recargo', 12, 2)->default(0);          // recargo financiero (tarjeta en cuotas)
            $table->decimal('monto_total', 12, 2);                  // lo que efectivamente paga el cliente

            $table->string('referencia_externa')->nullable();       // id de pago MP / MODO, nro de operación, cupón
            $table->string('url_pago', 1024)->nullable();           // init_point / checkout de la pasarela
            $table->string('comprobante')->nullable();              // archivo de comprobante de transferencia
            $table->json('datos_pasarela')->nullable();             // respuesta cruda de la pasarela
            $table->string('observaciones', 500)->nullable();
            $table->string('motivo_rechazo', 255)->nullable();

            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();

            $table->index(['estado', 'medio_pago']);
            $table->index('fecha_aprobacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
