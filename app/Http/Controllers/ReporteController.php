<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Reparacion;
use App\Models\Tecnico;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reportes / indicadores (punto 13 de la guía).
 *  - Recaudación por medio de pago (con gráficos): cruza pagos, órdenes y medios.
 *  - Productividad de técnicos: cruza órdenes, técnicos, tiempos y facturación.
 */
class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function recaudacion(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);

        $aprobados = Pago::aprobados()->whereBetween('fecha_aprobacion', [$desde, $hasta]);

        $porMedio = (clone $aprobados)
            ->select('medio_pago', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(monto_total) as total'), DB::raw('SUM(recargo) as recargos'))
            ->groupBy('medio_pago')
            ->orderByDesc('total')
            ->get()
            ->map(function ($fila) {
                $fila->medio_texto = Pago::MEDIOS[$fila->medio_pago] ?? $fila->medio_pago;
                return $fila;
            });

        $porDia = (clone $aprobados)
            ->select(DB::raw('DATE(fecha_aprobacion) as dia'), DB::raw('SUM(monto_total) as total'))
            ->groupBy('dia')
            ->orderBy('dia')
            ->pluck('total', 'dia');

        // Serie diaria completa (días sin cobros = 0) para el gráfico de evolución.
        $serie = [];
        for ($d = $desde->copy(); $d->lte($hasta); $d->addDay()) {
            $serie[$d->format('d/m')] = (float) ($porDia[$d->toDateString()] ?? 0);
        }

        $totalCobrado = (float) $porMedio->sum('total');
        $cantidad = (int) $porMedio->sum('cantidad');

        // Cuentas a cobrar: órdenes listas o entregadas con saldo.
        $ordenesConSaldo = Reparacion::with('cliente')
            ->whereIn('estado', ['listo', 'entregado'])
            ->get()
            ->filter(fn ($o) => $o->saldo > 0)
            ->sortByDesc('saldo');

        $indicadores = [
            'total_cobrado'     => $totalCobrado,
            'cantidad_cobros'   => $cantidad,
            'ticket_promedio'   => $cantidad ? $totalCobrado / $cantidad : 0,
            'recargos'          => (float) $porMedio->sum('recargos'),
            'pendiente_acreditar' => (float) Pago::pendientes()->sum('monto_total'),
            'por_cobrar'        => (float) $ordenesConSaldo->sum('saldo'),
            'medio_principal'   => $porMedio->first()?->medio_texto ?? '—',
            'porcentaje_digital' => $totalCobrado
                ? $porMedio->whereIn('medio_pago', ['mercado_pago', 'modo', 'transferencia'])->sum('total') * 100 / $totalCobrado
                : 0,
        ];

        return view('reportes.recaudacion', compact('desde', 'hasta', 'porMedio', 'serie', 'indicadores', 'ordenesConSaldo'));
    }

    public function tecnicos(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);

        $filas = Tecnico::withTrashed()->get()->map(function (Tecnico $tecnico) use ($desde, $hasta) {
            $ordenes = $tecnico->reparaciones()
                ->whereBetween('fecha_ingreso', [$desde->toDateString(), $hasta->toDateString()])
                ->get();

            $terminadas = $ordenes->whereIn('estado', ['listo', 'entregado']);
            $conEntrega = $ordenes->filter(fn ($o) => $o->fecha_entrega && $o->fecha_ingreso);
            $dias = $conEntrega->map(fn ($o) => $o->fecha_ingreso->diffInDays($o->fecha_entrega));

            return (object) [
                'tecnico'        => $tecnico->nombre_completo,
                'asignadas'      => $ordenes->count(),
                'terminadas'     => $terminadas->count(),
                'en_curso'       => $ordenes->whereIn('estado', ['ingresado', 'en_reparacion', 'esperando_repuesto'])->count(),
                'efectividad'    => $ordenes->count() ? $terminadas->count() * 100 / $ordenes->count() : 0,
                'dias_promedio'  => $dias->count() ? round($dias->avg(), 1) : null,
                'facturado'      => (float) $terminadas->sum(fn ($o) => $o->importe_a_cobrar),
            ];
        })->filter(fn ($f) => $f->asignadas > 0)->sortByDesc('terminadas')->values();

        return view('reportes.tecnicos', compact('desde', 'hasta', 'filas'));
    }

    private function rango(Request $request): array
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde)->startOfDay() : now()->subDays(29)->startOfDay();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta)->endOfDay() : now()->endOfDay();

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        return [$desde, $hasta];
    }
}
