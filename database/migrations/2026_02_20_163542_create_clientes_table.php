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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // Datos personales del cliente
            $table->string('nombre');
            $table->string('telefono');
            $table->string('whatsapp')->nullable();
            $table->string('email')->unique();
            $table->string('dni_cuit')->unique();

            // Notas internas (no visibles para el cliente)
            $table->text('notas_internas')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Para borrado lógico
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
