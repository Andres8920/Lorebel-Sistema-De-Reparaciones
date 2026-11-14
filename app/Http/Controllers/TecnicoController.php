<?php

namespace App\Http\Controllers;

use App\Models\Tecnico;
use Illuminate\Http\Request;

class TecnicoController extends Controller
{
    /** Mostrar lista de técnicos */
    public function index()
    {
        $tecnicos = Tecnico::orderBy('nombre')->paginate(20);
        return view('tecnicos.index', compact('tecnicos'));
    }

    /** Mostrar formulario de creación */
    public function create()
    {
        return view('tecnicos.create');
    }

    /** Guardar nuevo técnico */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'especialidad' => 'nullable|string|max:100',
            'activo' => 'sometimes|boolean',
        ]);

        $data['activo'] = $request->has('activo') ? (bool) $request->input('activo') : true;

        Tecnico::create($data);

        return redirect()->route('tecnicos.index')->with('success', 'Técnico creado correctamente.');
    }

    /** Mostrar detalle */
    public function show(Tecnico $tecnico)
    {
        return view('tecnicos.show', compact('tecnico'));
    }

    /** Formulario editar */
    public function edit(Tecnico $tecnico)
    {
        return view('tecnicos.edit', compact('tecnico'));
    }

    /** Actualizar */
    public function update(Request $request, Tecnico $tecnico)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'especialidad' => 'nullable|string|max:100',
            'activo' => 'sometimes|boolean',
        ]);

        $data['activo'] = $request->has('activo') ? (bool) $request->input('activo') : false;

        $tecnico->update($data);

        return redirect()->route('tecnicos.index')->with('success', 'Técnico actualizado correctamente.');
    }

    /** Eliminar técnico */
    public function destroy(Tecnico $tecnico)
    {
        $tecnico->delete();
        return redirect()->route('tecnicos.index')->with('success', 'Técnico eliminado.');
    }
}
