<?php

declare(strict_types=1);

namespace App\Http\Requests\Reportes;

use Illuminate\Foundation\Http\FormRequest;

final class ClienteDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.required'    => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date'        => 'La fecha de inicio no es válida.',
            'fecha_fin.required'       => 'La fecha de fin es obligatoria.',
            'fecha_fin.date'           => 'La fecha de fin no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}