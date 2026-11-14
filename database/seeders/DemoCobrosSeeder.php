<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Reparacion;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Datos de demostración para la Iteración 2: órdenes con cobros en distintos
 * medios de pago durante los últimos 45 días (alimenta los reportes).
 */
class DemoCobrosSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2026); // datos reproducibles

        $caja = User::where('rol', 'recepcion')->first();

        $clientes = collect([
            ['María Gómez', '341-155-1001', 'maria.gomez@email.com', '27-30111222-3'],
            ['Lucas Benítez', '341-155-1002', 'lucas.benitez@email.com', '20-33444555-6'],
            ['Sofía Rinaldi', '341-155-1003', 'sofia.rinaldi@email.com', '27-38777888-1'],
            ['Diego Acosta', '341-155-1004', 'diego.acosta@email.com', '20-29111333-4'],
            ['Valentina Paz', '341-155-1005', 'valentina.paz@email.com', '27-41222333-5'],
            ['Martín Ledesma', '341-155-1006', 'martin.ledesma@email.com', '20-35666777-8'],
        ])->map(fn ($c) => Cliente::create([
            'nombre' => $c[0], 'telefono' => $c[1], 'whatsapp' => $c[1], 'email' => $c[2], 'dni_cuit' => $c[3],
        ]));

        $equipos = [
            ['Smartphone', 'Samsung', 'Galaxy A54', 'Cambio de módulo de pantalla', 42000],
            ['Smartphone', 'Motorola', 'Moto G84', 'Reemplazo de batería', 18500],
            ['Smartphone', 'Apple', 'iPhone 12', 'Cambio de pin de carga', 26000],
            ['Notebook', 'Lenovo', 'IdeaPad 3', 'Limpieza y cambio de pasta térmica', 22000],
            ['Tablet', 'Samsung', 'Galaxy Tab A8', 'Reemplazo de táctil', 35000],
            ['Smartphone', 'Xiaomi', 'Redmi Note 12', 'Reparación de placa por humedad', 48000],
            ['Notebook', 'HP', 'Pavilion 15', 'Cambio de teclado', 39000],
            ['Smartphone', 'Apple', 'iPhone 13', 'Cambio de cámara trasera', 61000],
        ];

        $medios = ['efectivo', 'transferencia', 'mercado_pago', 'mercado_pago', 'modo', 'tarjeta_debito', 'tarjeta_credito'];

        for ($i = 0; $i < 28; $i++) {
            [$disp, $marca, $modelo, $trabajo, $importe] = $equipos[$i % count($equipos)];
            $diasAtras = mt_rand(2, 45);
            $ingreso = now()->subDays($diasAtras);
            $dias = mt_rand(1, 7);
            $entregada = $i % 5 !== 0;          // algunas quedan "listo" sin retirar
            $importe += mt_rand(-3, 3) * 1000;

            $orden = Reparacion::create([
                'cliente_id' => $clientes[$i % $clientes->count()]->id,
                'dispositivo' => $disp, 'marca' => $marca, 'modelo' => $modelo,
                'problema_reportado' => 'Falla reportada por el cliente: ' . strtolower($trabajo) . '.',
                'diagnostico_tecnico' => 'Diagnóstico confirmado: ' . strtolower($trabajo) . '.',
                'trabajo_realizado' => $trabajo . ' realizado y probado.',
                'estado' => 'listo',
                'presupuesto' => $importe, 'presupuesto_aprobado' => true,
                'fecha_aprobacion_presupuesto' => $ingreso,
                'tecnico_id' => ($i % 4) + 1,
                'fecha_ingreso' => $ingreso, 'fecha_estimada_entrega' => $ingreso->copy()->addDays($dias),
                'costo_repuestos' => round($importe * 0.55), 'costo_mano_obra' => $importe - round($importe * 0.55),
                'costo_total' => $importe,
            ]);

            $fechaPago = $ingreso->copy()->addDays(min($dias, $diasAtras - 1))->setTime(mt_rand(9, 19), mt_rand(0, 59));
            $medio = ! $entregada && $i % 10 === 0 ? 'transferencia' : $medios[($i * 3) % count($medios)];
            $medioSena = ['efectivo', 'transferencia', 'mercado_pago'][intdiv($i, 3) % 3];

            // Algunas órdenes con seña al ingreso (50 %) + saldo al retirar
            $tramos = $i % 3 === 0 ? [round($importe / 2), $importe - round($importe / 2)] : [$importe];

            foreach ($tramos as $n => $monto) {
                $medioTramo = $n === 0 && count($tramos) > 1 ? $medioSena : $medio;
                $cuotas = $medioTramo === 'tarjeta_credito' ? [1, 3, 6][mt_rand(0, 2)] : 1;
                $recargo = round($monto * (config('pagos.recargos_cuotas')[$cuotas] ?? 0) / 100, 2);
                $fecha = $n === 0 && count($tramos) > 1 ? $ingreso->copy()->setTime(10, 0) : $fechaPago;

                $esUltimo = $n === count($tramos) - 1;
                $pendiente = $esUltimo && ! $entregada && in_array($medioTramo, ['transferencia'], true);
                if ($esUltimo && ! $entregada && ! $pendiente) {
                    continue; // orden lista, aún sin cobrar el saldo
                }

                Pago::create([
                    'reparacion_id' => $orden->id, 'user_id' => $caja?->id,
                    'medio_pago' => $medioTramo, 'estado' => $pendiente ? 'pendiente' : 'aprobado',
                    'monto' => $monto, 'cuotas' => $cuotas, 'recargo' => $recargo, 'monto_total' => $monto + $recargo,
                    'referencia_externa' => $pendiente ? null : strtoupper(substr($medioTramo, 0, 3)) . '-' . mt_rand(100000, 999999),
                    'fecha_aprobacion' => $pendiente ? null : $fecha,
                    'created_at' => $fecha, 'updated_at' => $fecha,
                ]);
            }

            if ($entregada) {
                $orden->update(['estado' => 'entregado', 'fecha_entrega' => $fechaPago->toDateString()]);
            }
        }
    }
}
