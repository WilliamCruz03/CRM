<?php

declare(strict_types=1);

namespace App\Http\Requests\Reportes;

use App\Support\EanNormalizer;
use Illuminate\Foundation\Http\FormRequest;

final class BusquedaArticuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permisos existentes de reportes (ver/mostrar). No se crea permiso nuevo.
        return true;
    }

    public function rules(): array
    {
        return [
            'ean'          => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'per_page'     => ['nullable', 'integer', 'in:5,10,15,20,50'],
            'sort_by'      => [
                'nullable',
                'string',
                'in:compras_desc,compras_asc,frecuencia_asc,frecuencia_desc,ultima_compra_desc,monto_desc',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ean.required'          => 'El código de barras es obligatorio.',
            'ean.string'            => 'El código de barras debe ser texto.',
            'ean.max'               => 'El código de barras no puede superar los 50 caracteres.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date'     => 'La fecha de inicio no es válida.',
            'fecha_fin.required'    => 'La fecha de fin es obligatoria.',
            'fecha_fin.date'        => 'La fecha de fin no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'per_page.in'           => 'La cantidad por página debe ser 5, 10, 15, 20 o 50.',
            'sort_by.in'            => 'El criterio de orden no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('ean')) {
            $this->merge([
                'ean' => EanNormalizer::normalizar((string) $this->input('ean')),
            ]);
        }
    }
}