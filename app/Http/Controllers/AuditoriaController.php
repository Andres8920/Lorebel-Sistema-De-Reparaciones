<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Pago;
use App\Models\RegistroSesion;
use App\Models\Reparacion;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Reportes de auditoría (Anexo Auditoría - TFI). Acceso solo administrador.
 */
class AuditoriaController extends Controller
{
    /** Trazabilidad general con filtros. */
    public function index(Request $request)
    {
        $query = Auditoria::with('usuario')->latest('created_at')->latest('id');

        foreach (['entidad', 'accion', 'user_id', 'entidad_id'] as $filtro) {
            if ($request->filled($filtro)) {
                $query->where($filtro, $request->input($filtro));
            }
        }
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        $registros = $query->paginate(25)->withQueryString();
        $usuarios = User::orderBy('name')->get();

        return view('auditoria.index', compact('registros', 'usuarios'));
    }

    /**
     * Trazabilidad completa de una orden (elemento crítico): su estado original
     * y todas sus transformaciones, incluidos los cobros asociados.
     */
    public function orden(int $id)
    {
        $orden = Reparacion::withTrashed()->with('cliente')->findOrFail($id);
        $pagosIds = Pago::where('reparacion_id', $id)->pluck('id');

        $eventos = Auditoria::with('usuario')
            ->where(function ($q) use ($id, $pagosIds) {
                $q->where(fn ($q) => $q->where('entidad', 'Reparacion')->where('entidad_id', $id))
                  ->orWhere(fn ($q) => $q->where('entidad', 'Pago')->whereIn('entidad_id', $pagosIds));
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $estadoOriginal = $eventos->firstWhere(fn ($e) => $e->entidad === 'Reparacion' && $e->accion === 'alta')?->valores_nuevos;

        return view('auditoria.orden', compact('orden', 'eventos', 'estadoOriginal'));
    }

    /** Auditoría de login / logout. */
    public function sesiones(Request $request)
    {
        $query = RegistroSesion::with('usuario')->latest('created_at')->latest('id');

        if ($request->filled('evento')) {
            $query->where('evento', $request->evento);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        $registros = $query->paginate(25)->withQueryString();
        $usuarios = User::orderBy('name')->get();
        $resumen = [
            'login'         => RegistroSesion::where('evento', 'login')->whereDate('created_at', today())->count(),
            'logout'        => RegistroSesion::where('evento', 'logout')->whereDate('created_at', today())->count(),
            'login_fallido' => RegistroSesion::where('evento', 'login_fallido')->whereDate('created_at', today())->count(),
        ];

        return view('auditoria.sesiones', compact('registros', 'usuarios', 'resumen'));
    }
}
