<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anexo Auditoría (TFI).
 *  - auditorias: trazabilidad completa de elementos críticos (órdenes y pagos).
 *  - registro_sesiones: auditoría de login / logout / intentos fallidos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('usuario_nombre')->nullable();            // se conserva aunque el usuario se elimine
            $table->string('accion', 30);                            // alta | modificacion | baja | restauracion
            $table->string('entidad', 60);                           // Reparacion | Pago | Cliente
            $table->unsignedBigInteger('entidad_id');
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entidad', 'entidad_id']);
            $table->index('created_at');
        });

        Schema::create('registro_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->enum('evento', ['login', 'logout', 'login_fallido']);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'evento']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_sesiones');
        Schema::dropIfExists('auditorias');
    }
};
