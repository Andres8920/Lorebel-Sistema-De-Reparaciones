<?php

namespace App\Http\Controllers;

use App\Models\Reparacion;
use App\Models\Tecnico;
use App\Http\Requests\OrdenRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrdenController extends Controller
{
    /**
     * Muestra el listado de órdenes de reparación
     */
    public function index(Request $request)
    {
        $query = Reparacion::with(['cliente', 'tecnico']);

        // Filtro por estado
        if ($request->has('estado') && $request->estado != '') {
            $query->where('estado', $request->estado);
        }

        // Filtro por técnico
        if ($request->has('tecnico_id') && $request->tecnico_id != '') {
            $query->where('tecnico_id', $request->tecnico_id);
        }

        // Búsqueda por cliente, marca, modelo o IMEI
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhere('imei_serial', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        $ordenes = $query->latest('fecha_ingreso')->paginate(15);
        $tecnicos = Tecnico::activos()->get();

        return view('ordenes.index', compact('ordenes', 'tecnicos'));
    }

    /**
     * Muestra el formulario para crear una nueva orden
     */
    public function create()
    {
        $tecnicos = Tecnico::activos()->get();

        return view('ordenes.create', compact('tecnicos'));
    }

    /**
     * Almacena una nueva orden en la base de datos
     */
    public function store(OrdenRequest $request)
    {
        $data = $request->validated();

        // Si el checkbox de presupuesto aprobado no se envió, guardamos null (sin decisión aún)
        if (!$request->has('presupuesto_aprobado')) {
            $data['presupuesto_aprobado'] = null;
        }

        // Manejo de fotos
        if ($request->hasFile('fotos')) {
            $fotos = [];
            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('ordenes/fotos', 'public');
                $fotos[] = $path;
            }
            $data['fotos'] = $fotos;
        }

        // RN09: no se puede registrar una orden ya entregada con importe sin cobrar.
        if (($data['estado'] ?? null) === 'entregado' && (new Reparacion($data))->importe_a_cobrar > 0) {
            return back()->withInput()->with('error', 'No se puede registrar la orden como entregada: tiene importe pendiente de cobro. Regístrela como "Listo" y cobre desde la orden.');
        }

        $orden = Reparacion::create($data);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Orden de reparación creada exitosamente.');
    }

    /**
     * Muestra los detalles de una orden específica
     */
    public function show(Reparacion $orden)
    {
        $orden->load(['cliente', 'tecnico', 'pagos.usuario']);

        return view('ordenes.show', compact('orden'));
    }

    /**
     * Muestra el formulario para editar una orden
     */
    public function edit(Reparacion $orden)
    {
        $tecnicos = Tecnico::activos()->get();

        return view('ordenes.edit', compact('orden', 'tecnicos'));
    }

    /**
     * Actualiza una orden en la base de datos
     */
    public function update(OrdenRequest $request, Reparacion $orden)
    {
        $data = $request->validated();

        // Si el checkbox no se envió, el presupuesto no fue aprobado → null (sin decisión)
        // Si se envió con value="1", fue aprobado → true
        if (!$request->has('presupuesto_aprobado')) {
            $data['presupuesto_aprobado'] = null;
        }

        // Manejo de fotos nuevas
        if ($request->hasFile('fotos')) {
            $fotosExistentes = $orden->fotos ?? [];
            $fotosNuevas = [];

            foreach ($request->file('fotos') as $foto) {
                $path = $foto->store('ordenes/fotos', 'public');
                $fotosNuevas[] = $path;
            }

            // Combinar fotos existentes con nuevas
            $data['fotos'] = array_merge($fotosExistentes, $fotosNuevas);
        }

        // RN09: el equipo no puede entregarse con saldo pendiente de cobro.
        $orden->fill($data);
        if ($orden->estado === 'entregado' && $orden->isDirty('estado') && $orden->saldo > 0) {
            return back()->withInput()->with('error', sprintf(
                'No se puede entregar el equipo: la orden tiene un saldo pendiente de $ %s. Registre el cobro antes de entregar.',
                number_format($orden->saldo, 2, ',', '.')
            ));
        }

        // Registrar fecha de entrega automáticamente
        if ($orden->estado === 'entregado' && $orden->isDirty('estado') && ! $orden->fecha_entrega) {
            $orden->fecha_entrega = now();
        }

        $orden->save();

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Orden actualizada exitosamente.');
    }

    /**
     * Elimina una orden (borrado lógico)
     */
    public function destroy(Reparacion $orden)
    {
        $orden->delete();

        return redirect()->route('ordenes.index')
            ->with('success', 'Orden eliminada exitosamente.');
    }

    /**
     * Elimina una foto de la orden
     */
    public function eliminarFoto(Request $request, Reparacion $orden)
    {
        $fotoIndex = $request->input('foto_index');
        $fotos = $orden->fotos ?? [];

        if (isset($fotos[$fotoIndex])) {
            // Eliminar archivo físico
            Storage::disk('public')->delete($fotos[$fotoIndex]);

            // Eliminar del array
            unset($fotos[$fotoIndex]);
            $fotos = array_values($fotos); // Reindexar

            $orden->update(['fotos' => $fotos]);
        }

        return back()->with('success', 'Foto eliminada exitosamente.');
    }

    /**
     * Genera el comprobante PDF de la orden
     */
    public function generarPDF(Reparacion $orden)
    {
        $orden->load(['cliente', 'tecnico']);

        // Por ahora retornamos una vista HTML
        // Más adelante se puede integrar una librería de PDF como dompdf o barryvdh/laravel-dompdf
        return view('ordenes.pdf', compact('orden'));
    }
}
