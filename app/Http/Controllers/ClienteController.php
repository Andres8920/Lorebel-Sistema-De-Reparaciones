<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Requests\ClienteRequest;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Muestra el listado de clientes
     */
    public function index(Request $request)
    {
        $query = Cliente::with('reparaciones');

        // Búsqueda por nombre, email o DNI/CUIT
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('dni_cuit', 'like', "%{$search}%");
            });
        }

        $clientes = $query->latest()->paginate(10);

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Muestra el formulario para crear un nuevo cliente
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Almacena un nuevo cliente en la base de datos
     */
    public function store(ClienteRequest $request)
    {
        Cliente::create($request->validated());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Muestra los detalles de un cliente específico
     */
    public function show(Cliente $cliente)
    {
        $cliente->load('historialReparaciones');

        return view('clientes.show', compact('cliente'));
    }

    /**
     * Muestra el formulario para editar un cliente
     */
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Actualiza un cliente en la base de datos
     */
    public function update(ClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Elimina un cliente (borrado lógico)
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Búsqueda de clientes vía AJAX (autocompletado)
     * GET /clientes/buscar?q=texto
     */
    public function buscar(Request $request)
    {
        $q = $request->input('q', '');

        $clientes = Cliente::where('nombre', 'like', "%{$q}%")
            ->orWhere('telefono', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orWhere('dni_cuit', 'like', "%{$q}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get(['id', 'nombre', 'telefono', 'email']);

        return response()->json($clientes);
    }
}
