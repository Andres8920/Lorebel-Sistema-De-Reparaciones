<?php

namespace App\Http\Requests;

use App\Models\Pago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación de formato del formulario de cobro (caja negra: particiones y límites).
 * Las reglas de negocio se validan en PagoService::validarPago().
 */
class PagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medio_pago'    => ['required', Rule::in(array_keys(Pago::MEDIOS))],
            'monto'         => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'cuotas'        => ['nullable', 'integer', Rule::in(array_keys(config('pagos.recargos_cuotas')))],
            'referencia'    => ['nullable', 'required_if:medio_pago,tarjeta_debito,tarjeta_credito', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'medio_pago.required' => 'Seleccione un medio de pago.',
            'medio_pago.in'       => 'El medio de pago seleccionado no es válido.',
            'monto.required'      => 'Ingrese el monto a cobrar.',
            'monto.numeric'       => 'El monto debe ser numérico.',
            'monto.min'           => 'El monto debe ser mayor a cero.',
            'monto.max'           => 'El monto ingresado excede el máximo permitido.',
            'cuotas.in'           => 'La cantidad de cuotas no está habilitada.',
            'referencia.required_if' => 'Ingrese el número de cupón de la terminal de tarjetas.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Admite montos ingresados con coma decimal (formato argentino).
        if (is_string($this->monto)) {
            $monto = str_replace(['$', ' '], '', $this->monto);
            if (str_contains($monto, ',')) {
                $monto = str_replace(['.', ','], ['', '.'], $monto);
            }
            $this->merge(['monto' => $monto]);
        }
        if ($this->medio_pago !== 'tarjeta_credito') {
            $this->merge(['cuotas' => 1]);
        }
    }
}
