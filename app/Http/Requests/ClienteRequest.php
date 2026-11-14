<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClienteRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición
     */
    public function authorize(): bool
    {
        return true; // Cambiar según la lógica de autenticación
    }

    /**
     * Obtiene las reglas de validación para la petición
     */
    public function rules(): array
    {
        $clienteId = $this->route('cliente'); // ID del cliente en edición

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clientes')->ignore($clienteId),
            ],
            'dni_cuit' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clientes')->ignore($clienteId),
            ],
            'notas_internas' => ['nullable', 'string'],
        ];
    }

    /**
     * Mensajes de validación personalizados en español
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Este email ya está registrado.',
            'dni_cuit.required' => 'El DNI/CUIT es obligatorio.',
            'dni_cuit.unique' => 'Este DNI/CUIT ya está registrado.',
        ];
    }
}
