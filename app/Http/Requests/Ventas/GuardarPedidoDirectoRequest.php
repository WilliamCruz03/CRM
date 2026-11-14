<?php

namespace App\Http\Requests\Ventas;

use App\Models\Cliente;
use App\Models\Cotizaciones\CatConvenio;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Sucursal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarPedidoDirectoRequest extends FormRequest
{
    public function rules(): array
    {
        $categorias = (config('pedidos_directos.categorias') ?? (require config_path('pedidos_directos.php'))['categorias']);
        $tipo = $this->input('tipo_venta');
        $opciones = is_string($tipo) ? ($categorias[$tipo] ?? []) : [];

        return [
            'id_sucursal_origen' => [
                Rule::excludeIf((int) $this->user()->sucursal_asignada !== 0),
                'required', 'integer',
                Rule::in(OrdenPedido::SUCURSALES_SOBRE_PEDIDO),
                Rule::exists(Sucursal::class, 'id_sucursal'),
            ],
            'id_cliente' => ['required', 'integer', Rule::exists(Cliente::class, 'id_Cliente')->whereIn('status', Cliente::getActiveStatuses())],
            'anticipo_cliente' => 'required|numeric|decimal:0,2|min:0|max:9999999999.99',
            'fecha_encargo' => 'required|date_format:Y-m-d|before_or_equal:' . today()->toDateString(),
            'articulos' => 'required|array|min:1|max:100',
            'articulos.*.descripcion' => 'required|string|max:255',
            'tipo_venta' => ['required', Rule::in(array_keys($categorias))],
            'id_convenio' => [
                'exclude_unless:tipo_venta,convenio',
                'required',
                'integer',
                Rule::exists(CatConvenio::class, 'id')->where('status', 1)->where('tipo', 'C'),
            ],
            'articulos.*.ean_consultado' => 'nullable|string|regex:/^[0-9]{1,13}$/D',
            'articulos.*.categoria_promocion' => ['required', Rule::in(array_keys($opciones))],
            'articulos.*.cantidad' => 'required|integer|min:1|max:999',
            'articulos.*.precio_unitario' => 'required|numeric|decimal:0,2|min:0.01|max:99999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'articulos.*.cantidad.*' => 'La cantidad de cada artículo debe ser un número entero entre 1 y 999.',
            'fecha_encargo.*' => 'Selecciona una fecha de encargo válida que sea hoy o anterior.',
            'id_sucursal_origen.*' => 'Selecciona una sucursal de origen del listado.',
            'tipo_venta.required' => 'Selecciona Convenio o Público en general para el pedido.',
            'id_convenio.required' => 'Selecciona el convenio del pedido.',
            'id_convenio.exists' => 'Selecciona un convenio activo del listado.',
            'articulos.*.ean_consultado.regex' => 'El código de barras debe contener entre 1 y 13 dígitos.',
            'articulos.*.categoria_promocion.required' => 'Selecciona una categoría de descuento para cada artículo.',
            'articulos.*.categoria_promocion.in' => 'La categoría de descuento no corresponde al tipo de venta.',
        ];
    }
}
