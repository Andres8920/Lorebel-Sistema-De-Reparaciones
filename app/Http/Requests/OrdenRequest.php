<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrdenRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación para la petición
     */
    public function rules(): array
    {
        return [
            // Cliente
            'cliente_id' => ['required', 'exists:clientes,id'],

            // Equipo
            'dispositivo' => ['required', 'string', 'max:255'],
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'imei_serial' => ['nullable', 'string', 'max:255'],

            // Problema
            'problema_reportado' => ['required', 'string'],
            'diagnostico_tecnico' => ['nullable', 'string'],
            'trabajo_realizado' => ['nullable', 'string'],

            // Estado
            'estado' => ['required', 'in:ingresado,en_reparacion,esperando_repuesto,listo,entregado,cancelado'],

            // Fotos
            'fotos.*' => ['nullable', 'image', 'max:2048'], // Max 2MB por imagen

            // Presupuesto
            'presupuesto' => ['nullable', 'numeric', 'min:0'],
            'presupuesto_aprobado' => ['nullable', 'boolean'],
            'fecha_aprobacion_presupuesto' => ['nullable', 'date'],

            // Técnico
            'tecnico_id' => ['nullable', 'exists:tecnicos,id'],

            // Fechas
            'fecha_ingreso' => ['required', 'date'],
            'fecha_estimada_entrega' => ['nullable', 'date', 'after_or_equal:fecha_ingreso'],
            'fecha_entrega' => ['nullable', 'date'],

            // Costos
            'costo_repuestos' => ['nullable', 'numeric', 'min:0'],
            'costo_mano_obra' => ['nullable', 'numeric', 'min:0'],
            'costo_total' => ['nullable', 'numeric', 'min:0'], // calculado en prepareForValidation (antes no se persistía)
        ];
    }

    /**
     * Mensajes de validación personalizados en español
     */
    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'dispositivo.required' => 'El tipo de dispositivo es obligatorio.',
            'marca.required' => 'La marca es obligatoria.',
            'modelo.required' => 'El modelo es obligatorio.',
            'problema_reportado.required' => 'Debe describir el problema reportado.',
            'estado.required' => 'El estado es obligatorio.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_estimada_entrega.after_or_equal' => 'La fecha estimada debe ser igual o posterior a la fecha de ingreso.',
            'fotos.*.image' => 'El archivo debe ser una imagen.',
            'fotos.*.max' => 'Cada imagen no debe superar los 2MB.',
            'presupuesto.numeric' => 'El presupuesto debe ser un número.',
            'tecnico_id.exists' => 'El técnico seleccionado no existe.',
        ];
    }

    /**
     * Prepara los datos antes de la validación
     */
    protected function prepareForValidation(): void
    {
        // Calcular costo total si hay costos
        if ($this->has('costo_repuestos') || $this->has('costo_mano_obra')) {
            $costoRepuestos = (float) $this->input('costo_repuestos', 0);
            $costoManoObra = (float) $this->input('costo_mano_obra', 0);

            $this->merge([
                'costo_total' => $costoRepuestos + $costoManoObra,
            ]);
        }
    }
}
