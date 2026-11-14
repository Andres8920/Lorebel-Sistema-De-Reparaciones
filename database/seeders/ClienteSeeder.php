<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Reparacion;

class ClienteSeeder extends Seeder
{
    /**
     * Ejecuta el seeder de datos de ejemplo
     */
    public function run(): void
    {
        // Cliente 1 con historial
        $cliente1 = Cliente::create([
            'nombre' => 'Juan Carlos Pérez',
            'email' => 'juan.perez@email.com',
            'telefono' => '011-4555-1234',
            'whatsapp' => '11-5555-1234',
            'dni_cuit' => '20-35678901-2',
            'notas_internas' => 'Cliente frecuente, siempre puntual con los pagos.',
        ]);

        // Órdenes para cliente 1
        Reparacion::create([
            'cliente_id' => $cliente1->id,
            'dispositivo' => 'Smartphone',
            'marca' => 'Samsung',
            'modelo' => 'Galaxy S21',
            'imei_serial' => '356789012345678',
            'problema_reportado' => 'Pantalla rota tras caída. El táctil funciona parcialmente.',
            'diagnostico_tecnico' => 'Display LCD dañado. Necesita reemplazo completo.',
            'trabajo_realizado' => 'Pantalla reemplazada y calibrada. Protector de pantalla instalado.',
            'estado' => 'entregado',
            'fecha_ingreso' => now()->subDays(30),
            'fecha_estimada_entrega' => now()->subDays(25),
            'fecha_entrega' => now()->subDays(25),
            'presupuesto' => 25000.00,
            'presupuesto_aprobado' => true,
            'fecha_aprobacion_presupuesto' => now()->subDays(29),
            'tecnico_id' => 1,
            'costo_repuestos' => 18000.00,
            'costo_mano_obra' => 7000.00,
            'costo_total' => 25000.00,
        ]);

        Reparacion::create([
            'cliente_id' => $cliente1->id,
            'dispositivo' => 'Laptop',
            'marca' => 'HP',
            'modelo' => 'Pavilion 15',
            'imei_serial' => 'HPN5CD1234567',
            'problema_reportado' => 'No enciende, posible problema de batería.',
            'diagnostico_tecnico' => 'Batería completamente agotada. Carga al 0%. Necesita reemplazo.',
            'estado' => 'en_reparacion',
            'fecha_ingreso' => now()->subDays(2),
            'fecha_estimada_entrega' => now()->addDays(5),
            'presupuesto' => 15000.00,
            'presupuesto_aprobado' => true,
            'fecha_aprobacion_presupuesto' => now()->subDays(1),
            'tecnico_id' => 2,
        ]);

        // Cliente 2
        $cliente2 = Cliente::create([
            'nombre' => 'María González',
            'email' => 'maria.gonzalez@email.com',
            'telefono' => '011-4777-5678',
            'whatsapp' => '11-6777-5678',
            'dni_cuit' => '27-42123456-8',
            'notas_internas' => 'Prefiere presupuesto detallado antes de comenzar cualquier trabajo.',
        ]);

        Reparacion::create([
            'cliente_id' => $cliente2->id,
            'dispositivo' => 'Tablet',
            'marca' => 'Apple',
            'modelo' => 'iPad Air 4',
            'imei_serial' => 'DMQXXXXXXXXX',
            'problema_reportado' => 'No carga la batería. El cable se conecta pero no reconoce carga.',
            'diagnostico_tecnico' => 'Puerto de carga dañado por oxidación.',
            'trabajo_realizado' => 'Limpieza y reemplazo de puerto de carga.',
            'estado' => 'entregado',
            'fecha_ingreso' => now()->subDays(15),
            'fecha_estimada_entrega' => now()->subDays(10),
            'fecha_entrega' => now()->subDays(10),
            'presupuesto' => 8500.00,
            'presupuesto_aprobado' => true,
            'fecha_aprobacion_presupuesto' => now()->subDays(14),
            'tecnico_id' => 1,
            'costo_repuestos' => 5000.00,
            'costo_mano_obra' => 3500.00,
            'costo_total' => 8500.00,
        ]);

        // Cliente 3
        $cliente3 = Cliente::create([
            'nombre' => 'Roberto Martínez',
            'email' => 'roberto.martinez@email.com',
            'telefono' => '011-4888-9012',
            'dni_cuit' => '23-30567890-1',
            'notas_internas' => 'Cliente nuevo, derivado por Juan Pérez.',
        ]);

        Reparacion::create([
            'cliente_id' => $cliente3->id,
            'dispositivo' => 'Smartphone',
            'marca' => 'iPhone',
            'modelo' => '12 Pro',
            'imei_serial' => '359876543210987',
            'problema_reportado' => 'Batería se descarga muy rápido. Dura solo 2-3 horas.',
            'diagnostico_tecnico' => 'Batería degradada al 72% de capacidad.',
            'estado' => 'esperando_repuesto',
            'fecha_ingreso' => now()->subDays(5),
            'fecha_estimada_entrega' => now()->addDays(10),
            'presupuesto' => 12000.00,
            'presupuesto_aprobado' => true,
            'fecha_aprobacion_presupuesto' => now()->subDays(4),
            'tecnico_id' => 3,
        ]);

        // Cliente 4
        $cliente4 = Cliente::create([
            'nombre' => 'Ana Laura Fernández',
            'email' => 'ana.fernandez@email.com',
            'telefono' => '011-4999-3456',
            'whatsapp' => '11-7999-3456',
            'dni_cuit' => '27-38901234-5',
        ]);

        Reparacion::create([
            'cliente_id' => $cliente4->id,
            'dispositivo' => 'Smartphone',
            'marca' => 'Xiaomi',
            'modelo' => 'Redmi Note 10',
            'imei_serial' => '860123456789012',
            'problema_reportado' => 'Botón de encendido no funciona correctamente. Hay que presionar muy fuerte.',
            'estado' => 'ingresado',
            'fecha_ingreso' => now(),
            'fecha_estimada_entrega' => now()->addDays(7),
            'presupuesto' => 5000.00,
            'presupuesto_aprobado' => null,
        ]);

        // Cliente 5
        $cliente5 = Cliente::create([
            'nombre' => 'Carlos Rodríguez',
            'email' => 'carlos.rodriguez@email.com',
            'telefono' => '011-5111-2345',
            'whatsapp' => '11-8111-2345',
            'dni_cuit' => '20-33456789-0',
            'notas_internas' => 'Técnico informático, cliente muy exigente con la calidad.',
        ]);

        Reparacion::create([
            'cliente_id' => $cliente5->id,
            'dispositivo' => 'Laptop',
            'marca' => 'Lenovo',
            'modelo' => 'ThinkPad X1',
            'imei_serial' => 'LNVXXXXXXXXX',
            'problema_reportado' => 'Pantalla con líneas verticales y parpadeos intermitentes.',
            'diagnostico_tecnico' => 'Cable flex de pantalla dañado. Necesita reemplazo.',
            'trabajo_realizado' => 'Cable flex reemplazado. Pantalla funcionando correctamente.',
            'estado' => 'listo',
            'fecha_ingreso' => now()->subDays(3),
            'fecha_estimada_entrega' => now()->addDay(),
            'presupuesto' => 9500.00,
            'presupuesto_aprobado' => true,
            'fecha_aprobacion_presupuesto' => now()->subDays(2),
            'tecnico_id' => 2,
            'costo_repuestos' => 6000.00,
            'costo_mano_obra' => 3500.00,
            'costo_total' => 9500.00,
        ]);
    }
}
