<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reparaciones', function (Blueprint $table) {
            $table->id();

            // Relación con cliente
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            // Equipo - Detalles del dispositivo
            $table->string('dispositivo'); // Tipo: celular, tablet, laptop, etc
            $table->string('marca');
            $table->string('modelo');
            $table->string('imei_serial')->nullable(); // IMEI o número de serie

            // Problema y diagnóstico
            $table->text('problema_reportado'); // Lo que reporta el cliente
            $table->text('diagnostico_tecnico')->nullable(); // Análisis del técnico
            $table->text('trabajo_realizado')->nullable(); // Trabajo finalizado

            // Estado del trabajo
            $table->enum('estado', [
                'ingresado',
                'en_reparacion',
                'esperando_repuesto',
                'listo',
                'entregado',
                'cancelado'
            ])->default('ingresado');

            // Fotos del equipo (JSON con rutas de las imágenes)
            $table->json('fotos')->nullable();

            // Presupuesto y aprobación
            $table->decimal('presupuesto', 10, 2)->nullable();
            $table->boolean('presupuesto_aprobado')->nullable(); // null=pendiente, true=aprobado, false=rechazado
            $table->date('fecha_aprobacion_presupuesto')->nullable();

            // Técnico asignado
            $table->foreignId('tecnico_id')->nullable()->constrained('tecnicos')->onDelete('set null');

            // Fechas importantes
            $table->date('fecha_ingreso');
            $table->date('fecha_estimada_entrega')->nullable();
            $table->date('fecha_entrega')->nullable();

            // Costos finales
            $table->decimal('costo_repuestos', 10, 2)->nullable();
            $table->decimal('costo_mano_obra', 10, 2)->nullable();
            $table->decimal('costo_total', 10, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reparaciones');
    }
};
